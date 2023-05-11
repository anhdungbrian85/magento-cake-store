<?php

namespace X247Commerce\Checkout\Controller\Pickup;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Context;
use Amasty\Storelocator\Model\ResourceModel\Location\Collection as LocationCollection;
use Amasty\Storelocator\Model\ResourceModel\Location\CollectionFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use X247Commerce\Checkout\Api\StoreLocationContextInterface;
use X247Commerce\StoreLocator\Helper\DeliveryArea as DeliveryAreaHelper;
use Amasty\StorePickupWithLocator\Model\LocationPickupValues;
use Amasty\StorePickupWithLocator\Model\QuoteRepository;
use Magento\Checkout\Model\Session as CheckoutSession;

class Pickup extends \Amasty\Storelocator\Controller\Index\Ajax
{

    protected CustomerSession $customerSession;
    protected CollectionFactory $locationCollectionFactory;
    protected JsonFactory $resultJsonFactory;
    protected StoreLocationContextInterface $storeLocationContextInterface;
    protected DeliveryAreaHelper $deliveryAreaHelper;
    protected LocationPickupValues $locationPickupValues;
    protected QuoteRepository $pickupQuoteRepository;
    protected CheckoutSession $checkoutSession;
    
    public function __construct(
        CustomerSession $customerSession,
        CollectionFactory $locationCollectionFactory,
        JsonFactory $resultJsonFactory,
        StoreLocationContextInterface $storeLocationContextInterface,
        DeliveryAreaHelper $deliveryAreaHelper,
        LocationPickupValues $locationPickupValues,
        QuoteRepository $pickupQuoteRepository,
        CheckoutSession $checkoutSession,
        Context $context
    )
    {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customerSession = $customerSession;
        $this->locationCollectionFactory = $locationCollectionFactory;
        $this->storeLocationContextInterface = $storeLocationContextInterface;
        $this->deliveryAreaHelper = $deliveryAreaHelper;
        $this->locationPickupValues = $locationPickupValues;
        $this->quoteRepository = $pickupQuoteRepository;
        $this->checkoutSession = $checkoutSession;
    }

    
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $idQuote = $this->getRequest()->getParam('quoteId') ? $this->getRequest()->getParam('quoteId') : (int)$this->checkoutSession->getQuote()->getId();;
        $selectedDate = $this->getRequest()->getParam('selectedDate');
        $selectedTime = $this->getRequest()->getParam('selectedTime');
        if ($idQuote) {
            $pickupQuote = $this->quoteRepository->getByQuoteId($idQuote);
            if ($pickupQuote->getId()) {
                if ($selectedDate) {
                    $pickupQuote->setDate($selectedDate);
                    $pickupQuote->save();                
                    // $this->locationPickupValues->saveSelectedPickupData($idQuote, $pickupQuote);
                } elseif ($selectedTime) {
                    $timeFrom = substr($selectedTime, 0, strpos($selectedTime, '|'));
                    $timeTo = substr($selectedTime, (strpos($selectedTime, '|') + 1), strlen($selectedTime));
                    $pickupQuote->setTimeFrom($timeFrom);
                    $pickupQuote->setTimeTo($timeTo);
                    $pickupQuote->save();       
                }
                return $resultJson->setData(['idQuote' => $idQuote, 'pickupQuoteId' => $pickupQuote->getId(),'quoteDate' => $pickupQuote->getDate(), 'quoteTimeFrom' => $pickupQuote->getTimeFrom(), 'quoteTimeTo' => $pickupQuote->getTimeTo()]);
            } else {
                return $resultJson->setData(['error' => 'PickupQuote Not Exist']);
            }
        } else {
            return $resultJson->setData(['error' => 'Quote Not Exist']);
        }

    }
}