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

    public function __construct(
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
                $location = $this->getClosestStoreLocation($postcode);
                $logger->info('Location Id :' . $location->getId());
                if ($location && $location->getId()) {
                    $productSkus = [];
                    if (!empty($quote->getAllVisibleItems())) {
                        foreach ($quote->getAllVisibleItems() as $quoteItem) {
                            $productSkus[] = $quoteItem->getSku();
                        }
                    }
                    $logger->info('Location Id :' . $location->getId());
                    $closestLocationData = $this->locatorSourceResolver->getClosestLocationsHasProducts($location->getId(), $productSkus, 1);

                    if (isset($closestLocationData['current_source_is_available']) && $closestLocationData['current_source_is_available']) {
                        $this->storeLocationContext->setStoreLocationId($location->getId());
                    } else {
                        if (empty($closestLocationData['location_data'])) {
                            $quote->setTotalsCollectedFlag(false);
                            $quote->collectTotals();
                            throw new LocalizedException(__('There are no sources in the cart that match the items in the cart!'));
                        }else{
                            $closestLocation = $closestLocationData['location_data'][0];
                            $this->storeLocationContext->setStoreLocationId($closestLocation['amlocator_store']);
                        }
                    }

                } else{
                    throw new LocalizedException(__('There are no sources in the cart that match the items in the cart!'));
                }
            }
        } else {
            $locationId = $this->storeLocationContext->getStoreLocationId() ?? $this->checkoutSession->getStoreLocationId();
            if ($locationId) {
                foreach ($order->getAllItems() as $item) {
                    $available = $this->locatorSourceResolver->checkProductAvailableInStore($locationId, $item);
                    if (!$available) {
                        throw new LocalizedException(__('Some of the products are out stock!'));
                    }
                }
            } else {
                throw new LocalizedException(__('Please choose a store!'));
            }
        }
    }

    public function getClosestStoreLocation($postcode)
    {
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
        $location->addFieldtoFilter('id', ['in' => $deliverLocationsIds]);
        $location->applyDefaultFilters();
        return $location->getFirstItem();
    }
}
