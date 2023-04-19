<?php

namespace X247Commerce\Checkout\Observer\Quote;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use X247Commerce\Checkout\Api\StoreLocationContextInterface;
use Amasty\Storelocator\Model\LocationFactory;
use Amasty\StorePickupWithLocator\Model\QuoteFactory as PickupQuoteFactory;
use Magento\Quote\Model\Quote\AddressFactory;
use Amasty\StorePickupWithLocator\Api\QuoteRepositoryInterface as PickupQuoteRepositoryInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class UpdateStoreLocationSelected implements ObserverInterface
{
    protected $checkoutSession;
    protected $storeLocationContext;
    protected $logger;
    protected $locationFactory;
    protected $addressFactory;
    protected $pickupQuoteFactory;
    protected $pickupQuoteRepository;
    protected $timezone;

    public function __construct(
        CheckoutSession $checkoutSession,
        StoreLocationContextInterface $storeLocationContext,
        LocationFactory $locationFactory,
        AddressFactory $addressFactory,
        PickupQuoteFactory $pickupQuoteFactory,
        PickupQuoteRepositoryInterface $pickupQuoteRepository,
        TimezoneInterface $timezone,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->storeLocationContext = $storeLocationContext;
        $this->locationFactory = $locationFactory;
        $this->addressFactory = $addressFactory;
        $this->pickupQuoteFactory = $pickupQuoteFactory;
        $this->pickupQuoteRepository = $pickupQuoteRepository;
        $this->timezone = $timezone;
        $this->logger = $logger;

    }

    public function execute(EventObserver $observer)
    {
         
        if ($locationId = $this->storeLocationContext->getStoreLocationId()) {
            try {
                $quote = $this->checkoutSession->getQuote()
                    ->setData('store_location_id', $this->storeLocationContext->getStoreLocationId())
                    ->setData('delivery_type', $this->storeLocationContext->getDeliveryType())
                    ->setData('kl_sms_consent', 1)
                    ->setData('kl_email_consent', 1)
                    ->save();

                $quote = $this->checkoutSession->getQuote(); 
                // $this->logger->info('quote id: '. $this->checkoutSession->getQuote()->getId());
                // $this->logger->info('shippingAddress id: '. $quote->getShippingAddress()->getId());
                $shippingAddress = $this->addressFactory->create()->load($quote->getShippingAddress()->getId());

                $location = $this->locationFactory->create()->load($locationId);
                $deliveryType = $this->storeLocationContext->getDeliveryType();
                if ($deliveryType == 0 || $deliveryType == 2) {
                    $dataShippingAddress = [
                        'street' => $location->getData('address'),
                        'city' => $location->getData('city'),
                        'region' => $location->getData('state'),
                        'postcode' => $location->getData('zip'),
                        'country_id' => $location->getData('country'),
                        'telephone' => $location->getData('phone'),
                        'street' => $location->getData('address'),
                        'shipping_method' => 'amstorepickup_amstorepickup',
                        'shipping_description' => 'Collect in Store - Collect in Store',
                        'same_as_billing' => 0
                    ];

                    $shippingAddress->addData($dataShippingAddress)->save();
                    
                    $pickupQuote = $this->pickupQuoteRepository->getByAddressId($quote->getShippingAddress()->getId());
                    if (!$pickupQuote->getId()) {
                        $pickupQuote = $this->pickupQuoteFactory->create();
                    }
                    $leadDelivery = $this->getInitLeadDeliveryValue();
                    $today = $this->timezone->date(new \DateTime('now'));
                    $pickupDate = $today ;

                    $workingTime = $location->getWorkingTime(strtolower($pickupDate->format('l')));
                    
                    if ($workingTime) {
                        $openTime = explode(' - ', array_values($workingTime)[0])[0];
                        $closeTime = explode(' - ', array_values($workingTime)[0])[1];
                    }   else {
                        $openTime = '10:00';
                        $closeTime = '20:00';
                    }

                    $this->logger->info('xxxxxxxxxxxxxxxxx:'. $openTime);
                    $this->logger->info('xxxxxxxxxxxxxxxxx:'. $closeTime);
                    $nowH = $pickupDate->format('H');
                    $nowM = $pickupDate->format('i');

                    if (1 < $nowM && $nowM <= 30) {
                        $nearHalfTime = "$nowH:30";
                        $openTime = $nearHalfTime;
                    }  
                    if ($nowM > 30) {
                        $nowH++;
                        $nearHalfTime = "$nowH:00";
                        $openTime = $nearHalfTime;
                    }

                    $openTime = strtotime($pickupDate->format('Y-m-d ') .$openTime);
                    $closeTime = strtotime($pickupDate->format('Y-m-d ') .$closeTime);
                    
                    if ($openTime >= $closeTime) {
                        $pickupDate = $this->timezone->date(new \DateTime('+1 day'));
                        $workingTime = $location->getWorkingTime(strtolower($pickupDate->format('l')));
                    
                        if ($workingTime) {
                            $openTime = explode(' - ', array_values($workingTime)[0])[0] + 3600;
                        }   else {
                            $openTime = '11:00';
                        }
                    }

                    $pickupQuote->addData([
                        'address_id' => $shippingAddress->getId(),
                        'quote_id' => $quote->getId(),
                        'store_id' => $location->getId(),
                        'date' => $pickupDate->format('Y-m-d'),
                        'time_from' => $openTime,
                        'time_to' =>  (int) $openTime

                    ]);
                    
                    $pickupQuote->save();

                }   else {
                    $dataShippingAddress = [
                        'shipping_method' => 'flatrate_flatrate',
                        'shipping_description' => 'Premium Delivery',
                        'same_as_billing' => 0
                    ];
                    $shippingAddress->addData($dataShippingAddress)->save();
                }
                
            } catch (\Exception $e) {
                $this->logger->info('Cannot update shipping method: ' . $e->getMessage());
            }
            
        }
        
        return $this;
    }

    private function getInitLeadDeliveryValue()
    {
        $quote = $this->checkoutSession->getQuote();
        $leadDelivery = 0;
        foreach ($quote->getAllItems() as $item) {
            if ($leadDelivery < $item->getProduct()->getData('lead_delivery')) {
                $leadDelivery = $item->getProduct()->getData('lead_delivery');
            }
        }
        return $leadDelivery;
    }
}