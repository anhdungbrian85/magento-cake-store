<?php
namespace X247Commerce\Checkout\Observer\Quote;

use Amasty\CheckoutDeliveryDate\Model\DeliveryDateProvider;
use Amasty\StorePickupWithLocator\Model\QuoteRepository as AmPickupQuoteRepository;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Checkout\Model\Session as CheckoutSession;
use X247Commerce\StoreLocatorSource\Model\ResourceModel\LocatorSourceResolver;
use X247Commerce\Checkout\Api\StoreLocationContextInterface;
use Psr\Log\LoggerInterface;
use X247Commerce\StoreLocator\Helper\DeliveryArea as DeliveryAreaHelper;
use X247Commerce\Delivery\Helper\DeliveryData;
use Amasty\Storelocator\Model\ResourceModel\Location\CollectionFactory as LocationCollectionFactory;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class QuoteSubmitBefore implements ObserverInterface
{
    protected CheckoutSession $checkoutSession;
    protected LoggerInterface $logger;
    protected LocatorSourceResolver $locatorSourceResolver;
    protected StoreLocationContextInterface $storeLocationContext;
    protected LocationCollectionFactory $locationCollectionFactory;
    protected DeliveryAreaHelper $deliveryAreaHelper;
    protected DeliveryData $deliveryData;
    protected AmPickupQuoteRepository $amQuoteRepository;
    protected DeliveryDateProvider $deliveryDateProvider;
    protected File $file;
    protected $productRepository;
    protected $searchCriteriaBuilder;

    public function __construct(
        DeliveryData $deliveryData,
        DeliveryAreaHelper $deliveryAreaHelper,
        LocationCollectionFactory $locationCollectionFactory,
        CheckoutSession $checkoutSession,
        LocatorSourceResolver $locatorSourceResolver,
        StoreLocationContextInterface $storeLocationContext,
        LoggerInterface $logger,
        AmPickupQuoteRepository $amQuoteRepository,
        DeliveryDateProvider $deliveryDateProvider,
        File $file,
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->deliveryData = $deliveryData;
        $this->locationCollectionFactory = $locationCollectionFactory;
        $this->deliveryAreaHelper = $deliveryAreaHelper;
        $this->checkoutSession = $checkoutSession;
        $this->locatorSourceResolver = $locatorSourceResolver;
        $this->storeLocationContext = $storeLocationContext;
        $this->amQuoteRepository = $amQuoteRepository;
        $this->deliveryDateProvider = $deliveryDateProvider;
        $this->file = $file;
        $this->logger = $logger;
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }


    public function execute(EventObserver $observer): void
    {
        $order = $observer->getEvent()->getOrder();
        $quote = $observer->getEvent()->getQuote();
        $shippingMethod = $quote->getShippingAddress()->getShippingMethod();
        $shippingAddress = $quote->getShippingAddress();
        if ($shippingMethod == 'cakeboxdelivery_cakeboxdelivery') {

            $postcode = $shippingAddress->getPostcode();
            $deliveryQuote = $this->deliveryDateProvider->findByQuoteId($quote->getId());

            if (empty($deliveryQuote) || empty($deliveryQuote->getDate()) || empty($deliveryQuote->getTime())) {
                $this->logger->info('backend validation fired: cakeboxdelivery_cakeboxdelivery' . ' - '. $quote->getId(). ' - '. $deliveryQuote->getDate());
                throw new LocalizedException(__('Pickup, delivery time/date are required!'));
            }

            if ($postcode && $postcode != '-') {
                $productSkus = [];
                if (!empty($quote->getAllVisibleItems())) {
                    foreach ($quote->getAllVisibleItems() as $quoteItem) {
                        $productSkus[] = $quoteItem->getSku();
                    }
                }
                $locationDataFromPostCode = $this->deliveryData->getLongAndLatFromPostCode($postcode);
                if ($locationDataFromPostCode['status']) {
                    $locations = $this->locatorSourceResolver->getAllClosestStoreLocationsWithPostCodeAndSkus(
                        $postcode,
                        $locationDataFromPostCode['data']['lat'],
                        $locationDataFromPostCode['data']['lng'],
                        $productSkus
                    );
                    $location = null;
                    foreach ($locations as $loc)
                    {
                        $isWeekend = (date('N', strtotime($deliveryQuote->getDate())) >= 6);
                        if ($this->locatorSourceResolver->checkStoreDeliveryAvaiable($loc->getId(), $deliveryQuote->getDate())) {
                            $location = $loc;
                            break;
                        }
                    }
                    if ($location != null && $location->getId()) {
                        $quote->setData('store_location_id', $location->getId());
                        $order->setData('store_location_id', $location->getId());
                    } else {
                        $quote->setTotalsCollectedFlag(false);
                        $quote->collectTotals();
                        throw new LocalizedException(__('We do not yet deliver to that area, or this store won\'t open this day. Please arrange to collect in-store or use another delivery address!'));
                    }
                }
            }
        } else {
            $locationId = $quote->getData('store_location_id') ?? $this->storeLocationContext->getStoreLocationId();
            $pickupQuote = $this->amQuoteRepository->getByQuoteId($quote->getId());

            if (empty($pickupQuote) || empty($pickupQuote->getDate()) || empty($pickupQuote->getTimeFrom())) {
                $this->logger->info('backend validation fired: amstorepickup' . ' - '. $quote->getId(). ' - '. $pickupQuote->getDate());
                throw new LocalizedException(__('Pickup, delivery time/date are required!'));
            }

            if ($locationId) {
                foreach ($order->getAllItems() as $item) {
                    //$this->logger->info('Item at checkout:'.$item->getSku());
                    $this->logger->info('Item type :'.$item->getProductType());
                    if($item->getProductType() == 'bundle'){
                        $productIds = [];
                        $productId = $item->getData('product_id');
                        foreach($quote->getAllItems() as $quoteItem){
                            if($quoteItem->getData('product_id') == $productId){
                                $quoteData = $quoteItem->getChildren();
                            }
                        }
                        $children = $quoteData;
                        foreach ($children as $child) {
                            $childProduct = $child->getProduct();
                            /** Access information about each child product  */
                            $productIds[] = $childProduct->getId();
                        }
                        //$this->logger->info('Item  :'.print_r($productIds, true));
                        $newChildData = $this->loadProductsByIds($productIds);
                        foreach($newChildData as $childData){
                                if (!$this->locatorSourceResolver->checkProductAvailableInStore($locationId, $childData)) {
                                    $logger->info('Error current product: ' . $childData->getSku() );
                                    throw new LocalizedException(__('Some of the products are out stock!'));
                                }
                         }
                                                
                    }else{
                        $available = $this->locatorSourceResolver->checkProductAvailableInStore($locationId, $item);
                    
                        if (!$available) {
                            throw new LocalizedException(__('Some of the products are out stock!'));
                        }
                    }
                }
                if ($quote->getData('store_location_id')) {
                    $order->setData('store_location_id', $quote->getData('store_location_id'));
                }
            } else {
                throw new LocalizedException(__('Please choose a store!'));
            }
        }
        $this->validateProductImageUploaded($observer);

    }

    /**
     * @return void
     */
    private function validateProductImageUploaded($observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $items = $quote->getAllVisibleItems();
        foreach ($items as $item) {
            $options = $item->getOptions();
            foreach ($options as $option) {
                $option = json_decode($option->getValue(), true);
                if (!empty($option['options'])) {
                    $customOptions = $option['options'];
                    foreach ($customOptions as $customOption) {
                        if (is_array($customOption) && !empty($customOption['type']) && strpos($customOption['type'], 'image') != -1) {
                            if (empty($customOption['fullpath']) || !$this->file->isExists($customOption['fullpath'])) {
                                throw new LocalizedException(__('Seems your uploaded photo is missing, please check and re-upload!'));
                            }
                        }
                    }
                }
            }
        }
    }

    public function loadProductsByIds(array $productIds)
    {
        // Build search criteria
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('entity_id', $productIds, 'in')
            ->create();

        // Load products
        $products = $this->productRepository->getList($searchCriteria)->getItems();

        /** $products now contains the loaded products */

        return $products;
    }
}
