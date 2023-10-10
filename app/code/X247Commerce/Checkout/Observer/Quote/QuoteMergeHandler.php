<?php
namespace X247Commerce\Checkout\Observer\Quote;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use X247Commerce\Catalog\Model\ProductSourceAvailability;
use X247Commerce\StoreLocatorSource\Model\ResourceModel\LocatorSourceResolver;
use X247Commerce\Checkout\Api\StoreLocationContextInterface;
use Psr\Log\LoggerInterface;
use X247Commerce\StoreLocator\Helper\DeliveryArea as DeliveryAreaHelper;
use X247Commerce\Delivery\Helper\DeliveryData;

class QuoteMergeHandler implements ObserverInterface
{
    protected $checkoutSession;
    protected $logger;
    protected $sourceRepository;
    protected $searchCriteriaBuilderFactory;
    protected $productSourceAvailability;
    protected $locatorSourceResolver;
    protected $storeLocationContext;
    protected $locationCollectionFactory;
    protected $deliveryAreaHelper;
    protected $deliveryData;

    public function __construct(
        DeliveryData $deliveryData,
        DeliveryAreaHelper $deliveryAreaHelper,
        \Amasty\Storelocator\Model\ResourceModel\Location\CollectionFactory $locationCollectionFactory,
        CheckoutSession $checkoutSession,
        SourceRepositoryInterface $sourceRepository,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        ProductSourceAvailability $productSourceAvailability,
        LocatorSourceResolver $locatorSourceResolver,
        StoreLocationContextInterface $storeLocationContext,
        LoggerInterface $logger
    ) {
        $this->deliveryData = $deliveryData;
        $this->locationCollectionFactory = $locationCollectionFactory;
        $this->deliveryAreaHelper = $deliveryAreaHelper;
        $this->checkoutSession = $checkoutSession;
        $this->sourceRepository = $sourceRepository;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->productSourceAvailability = $productSourceAvailability;
        $this->locatorSourceResolver = $locatorSourceResolver;
        $this->storeLocationContext = $storeLocationContext;
        $this->logger = $logger;
    }

    public function execute(EventObserver $observer)
    {

        $currentQuote = $observer->getData('quote'); // older quote
        $newQuote = $observer->getData('source');

       $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/quote_merged.log');
       $logger = new \Zend_Log();
       $logger->addWriter($writer);
       $logger->info('Start quote_merged log');
//        $logger->info('quoteIdOld: '. $currentQuote->getId()); // quote cu
//        $logger->info('quoteIdNew: '. $newQuote->getId()); // quote moi
//        $logger->info('oldquoteshipping: '. $currentQuote->getShippingAddress()->getShippingMethod());
//        $logger->info('newquoteshipping: '. $newQuote->getShippingAddress()->getShippingMethod());


        if ($currentQuote->getShippingAddress()->getShippingMethod() != $newQuote->getShippingAddress()->getShippingMethod()) {
            $currentQuote->getShippingAddress()->setShippingMethod($newQuote->getShippingAddress()->getShippingMethod())->save();
        }

        $currentQuote->setData('store_location_id', $newQuote->getData('store_location_id'));
        $currentQuote->setData('delivery_type', $newQuote->getData('delivery_type'));
        if ($currentQuote->getCustomerId() && $currentQuote->getCustomerIsGuest()) {
            $currentQuote->setCustomerIsGuest(0)->save();
        }

    }
}
