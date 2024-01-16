<?php
namespace X247Commerce\StoreLocator\Observer\Cart;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use X247Commerce\StoreLocatorSource\Model\ResourceModel\LocatorSourceResolver;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class ValidateOutOfStockStatus implements ObserverInterface
{
    protected $checkoutSession;

    protected $locationContext;

    protected $locatorSourceResolver;

    protected $_productloader;

    protected $productRepository;
    protected $searchCriteriaBuilder;

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \X247Commerce\Checkout\Api\StoreLocationContextInterface $locationContext,
        LocatorSourceResolver $locatorSourceResolver,
        \Magento\Catalog\Model\ProductFactory $_productloader,
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->locationContext = $locationContext;
        $this->locatorSourceResolver = $locatorSourceResolver;
        $this->_productloader = $_productloader;
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/add_to_cart.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('Starting debug');
        $currentProduct = '';
        $selectedLocationId = $this->locationContext->getStoreLocationId();
        $quote = $this->checkoutSession->getQuote();
        $logger->info('Selected Location Id: ' . $selectedLocationId );
        $currentProduct = $observer->getEvent()->getProduct();
        $quoteItem = $observer->getEvent()->getQuoteItem();
        $logger->info('Current Produc Sku: ' . $currentProduct->getSku() );
        $logger->info('Product Type: ' . $currentProduct->getTypeId());
        $logger->info('Quote Item Id: ' . $quote->getId());
        /** Bundle logic SPTCAK-46 */
        if($currentProduct->getTypeId() == 'bundle'){
            $logger->info('Starting Bundle');
            $logger->info('store location id:'. $selectedLocationId);
            $productIds = [];
           // $logger->info('Bundle Item Id: ' . json_encode($currentProduct->getData(), true) );
            foreach($quote->getAllVisibleItems() as $bundleitem) {
                //$logger->info('All Item data: ' . json_encode($bundleitem->getData(), true) );
                $logger->info('inside foreach');
                $logger->info('Entity Id:'. $currentProduct->getData('entity_id'));
                $logger->info('Product Id:'. $bundleitem->getData('product_id'));
                if($currentProduct->getData('entity_id') == $bundleitem->getData('product_id')){
                    $itemOptions = $bundleitem->getdata('qty_options');

                    foreach ($itemOptions as $key => $value) {
                        $productIds[] = $key;
                    }
                    //$logger->info('Parent Item Id: ' . print_r($productIds, true) );
                }
            }
            $logger->info('Parent item id before foreach: ' . print_r($productIds, true) );
            $newChildData = $this->loadProductsByIds($productIds);
             foreach($newChildData as $childData){
                $logger->info('child item : ' .$childData->getSku() );
                //$childData = $this->getLoadProduct($childId);
                    if (!$this->locatorSourceResolver->validateOutOfStockStatusOfProduct($selectedLocationId, $childData->getSku())) {
                        $logger->info('Error current product: ' . $childData->getSku() );
                        throw new \Magento\Framework\Exception\LocalizedException(__('The current product is out of stock on this location.'));
                    }

                    if (!$this->locatorSourceResolver->checkProductAvailableInStore($selectedLocationId, $childData)) {
                        $logger->info('Error sku: ' . $childData->getSku() );
                        throw new \Magento\Framework\Exception\LocalizedException(__('The product is out of stock on this location.'));
                    }
             }
         }else{
            $logger->info('Starting Non-bundle');
            $logger->info('store location id:'. $selectedLocationId);
            $logger->info('Current product id: '. $currentProduct->getSku());
            if (!$this->locatorSourceResolver->validateOutOfStockStatusOfProduct($selectedLocationId, $currentProduct->getSku())) {
                $logger->info('Error current product: ' . $currentProduct->getSku() );
                throw new \Magento\Framework\Exception\LocalizedException(__('The current product is out of stock on this location.'));
            }
            $productData = $this->getLoadProduct($currentProduct->getId());
           // $logger->info('Parent Item Id: ' . json_encode($currentProduct->getData(), true) );
            if (!$this->locatorSourceResolver->checkProductAvailableInStore($selectedLocationId, $productData)) {
                $logger->info('Error sku: ' . $currentProduct->getId() );
                throw new \Magento\Framework\Exception\LocalizedException(__('The product is out of stock on this location.'));
            }
         }


        $logger->info('Ending debug');
    }

    public function getLoadProduct($id)
    {
        return $this->_productloader->create()->load($id);
    }

    public function loadProductsByIds(array $productIds)
    {
        // Build search criteria
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('entity_id', $productIds, 'in')
            ->create();

        // Load products
        $products = $this->productRepository->getList($searchCriteria)->getItems();

        // $products now contains the loaded products

        return $products;
    }
}
