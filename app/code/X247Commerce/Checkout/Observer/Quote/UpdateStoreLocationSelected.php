<?php

namespace X247Commerce\Checkout\Observer\Quote;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Psr\Log\LoggerInterface;
use X247Commerce\Checkout\Api\StoreLocationContextInterface;
use Amasty\Storelocator\Model\LocationFactory;
use Amasty\StorePickupWithLocator\Model\QuoteFactory as PickupQuoteFactory;
use Magento\Quote\Model\Quote\AddressFactory;
use Amasty\StorePickupWithLocator\Api\QuoteRepositoryInterface as PickupQuoteRepositoryInterface;

class UpdateStoreLocationSelected implements ObserverInterface
{
    protected CheckoutSession $checkoutSession;
    protected StoreLocationContextInterface $storeLocationContext;
    protected LoggerInterface $logger;
    protected LocationFactory $locationFactory;
    protected AddressFactory $addressFactory;
    protected PickupQuoteFactory $pickupQuoteFactory;
    protected PickupQuoteRepositoryInterface $pickupQuoteRepository;

    public function __construct(
        CheckoutSession $checkoutSession,
        StoreLocationContextInterface $storeLocationContext,
        LocationFactory $locationFactory,
        AddressFactory $addressFactory,
        PickupQuoteFactory $pickupQuoteFactory,
        PickupQuoteRepositoryInterface $pickupQuoteRepository,
        LoggerInterface $logger
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->storeLocationContext = $storeLocationContext;
        $this->locationFactory = $locationFactory;
        $this->addressFactory = $addressFactory;
        $this->pickupQuoteFactory = $pickupQuoteFactory;
        $this->pickupQuoteRepository = $pickupQuoteRepository;
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
                $shippingAddress = $this->addressFactory->create()->load($quote->getShippingAddress()->getId());

                $location = $this->locationFactory->create()->load($locationId);
                $deliveryType = $this->storeLocationContext->getDeliveryType();

                if ($deliveryType == 0 || $deliveryType == 2) {
                    $dataShippingAddress = [
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


                    $pickupQuote->addData([
                        'address_id' => $shippingAddress->getId(),
                        'quote_id' => $quote->getId(),
                        'store_id' => $location->getId(),
                    ]);

                    $pickupQuote->save();

                }  else {
                    $dataShippingAddress = [
                        'shipping_method' => 'cakeboxdelivery_cakeboxdelivery',
                        'shipping_description' => 'Cakebox Delivery',
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

}
