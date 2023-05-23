<?php
namespace X247Commerce\Checkout\Observer\Quote;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\PaymentException;
use Magento\Framework\Exception\LocalizedException;
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

class QuoteSubmitBefore implements ObserverInterface
{
    protected $checkoutSession;
    protected $logger;
    private $sourceRepository;
    private $searchCriteriaBuilderFactory;
    private $productSourceAvailability;
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
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/checkout_test.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('Start debugging!'); // Print string type data

        $order = $observer->getEvent()->getOrder();
        $quote = $observer->getEvent()->getQuote();
        $shippingMethod = $quote->getShippingAddress()->getShippingMethod();
        $shippingAddress = $quote->getShippingAddress();
        $logger->info('Shipping Method:' . $shippingMethod);
        if($shippingMethod == 'cakeboxdelivery_cakeboxdelivery') {
            $postcode = $shippingAddress->getPostcode();
            $logger->info('PostCode:' . $postcode);
            if($postcode && $postcode != '-'){
                $productSkus = [];
                if (!empty($quote->getAllVisibleItems())) {
                    foreach ($quote->getAllVisibleItems() as $quoteItem) {
                        $productSkus[] = $quoteItem->getSku();
                    }
                }
                $locationDataFromPostCode = $this->deliveryData->getLongAndLatFromPostCode($postcode);
                $logger->info('$customerPostcode' . $postcode);
                $logger->info('$locationDataFromPostCode'.print_r($locationDataFromPostCode, true));
                if ($locationDataFromPostCode['status']) {
                    $location = $this->locatorSourceResolver->getClosestStoreLocationWithPostCodeAndSkus(
                        $postcode,
                        $locationDataFromPostCode['data']['lat'],
                        $locationDataFromPostCode['data']['lng'],
                        $productSkus
                    );
                    if ($location->getId()) {
                        $quote->setData('store_location_id', $location->getId());
                        $order->setData('store_location_id', $location->getId());
                    } else {
                        $quote->setTotalsCollectedFlag(false);
                        $quote->collectTotals();
                        throw new LocalizedException(__('We do not yet deliver to that area. Please arrange to collect in-store or use another delivery address!'));
                    }
                }
            }
        } else {
            $locationId = $quote->getData('store_location_id');
			$logger->info('Collect in Store Location: '.$locationId);
            if ($locationId) {
                foreach ($order->getAllItems() as $item) {
					$logger->info('Collect in Store Location SKU: '.$item->getSku());
                    $available = $this->locatorSourceResolver->checkProductAvailableInStore($locationId, $item);
                    if (!$available) {
						$logger->info('Collect in Store Location SKU ERROR: '.$item->getSku());
                        throw new LocalizedException(__('Some of the products are out stock!'));
                    }
                }
                if ($quote->getData('store_location_id')) {
                    $order->setData('store_location_id', $quote->getData('store_location_id'));
                }
            } else {
                throw new LocalizedException(__('Please choose a store!'));
            }
        }
    }

    public function getClosestStoreLocation($postcode)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/checkout_test.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('PostCode: ' . $postcode);
        if (!$postcode) {
            return false;
        }
        $needToPrepareCollection = false;
        $location = $this->locationCollectionFactory->create()->addFieldToFilter('enable_delivery', ['eq' => 1]);
        $deliverLocations = $this->deliveryAreaHelper->getDeliverLocations($postcode);
        $deliverLocationsIds = [];

        foreach ($deliverLocations as $deliverLocation) {
            $deliverLocationsIds[] = $deliverLocation->getStoreId();
        }
        $logger->info('Deliver Locations Ids: ');
        $logger->info(print_r($deliverLocationsIds, true));

        $location->addFieldtoFilter('id', ['in' => $deliverLocationsIds]);
        $location->applyDefaultFilters();
        $logger->info('Collection query: ' . $location->getSelect());
        return $location->getFirstItem();
    }
}
