<?php

namespace X247Commerce\Checkout\Observer\Cart;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use X247Commerce\Catalog\Model\ProductSourceAvailability;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use X247Commerce\StoreLocatorSource\Model\ResourceModel\LocatorSourceResolver;

class ValidateAddToCart implements ObserverInterface
{
    protected $customerSession;
    private $sourceRepository;
    private $searchCriteriaBuilderFactory;
    private $productSourceAvailability;
    protected $_messageManager;
    protected $logger;
    protected $request;
    protected $product;
    protected $configurableproduct;
    protected $locatorSourceResolver;

    public function __construct(
        CustomerSession $customerSession,
        SourceRepositoryInterface $sourceRepository,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        ProductSourceAvailability $productSourceAvailability,
        LoggerInterface $logger,
        RequestInterface $request,
        Product $product,
        LocatorSourceResolver $locatorSourceResolver,
        Configurable $configurableproduct
    ) {
        $this->customerSession = $customerSession;
        $this->sourceRepository = $sourceRepository;
        $this->productSourceAvailability = $productSourceAvailability;
        $this->_messageManager = $messageManager;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->logger = $logger;
        $this->request = $request;
        $this->product = $product;
        $this->locatorSourceResolver = $locatorSourceResolver;
        $this->configurableproduct = $configurableproduct;
    }

    public function execute(EventObserver $observer)
    {
        $locationId = $this->customerSession->getStoreLocationId();

        $postValues = $this->request->getPostValue();
        $productId = $postValues['product'];
        $addProduct = $observer->getProduct();

        if ($locationId) {

            $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
            $searchCriteria = $searchCriteriaBuilder->addFilter('amlocator_store', $locationId, 'in')->create();
            $sources = $this->locatorSourceResolver->getSourceCodeByAmLocator($locationId);
            if (empty($sources)) {
                throw new LocalizedException(__("The product is not available in the selected store."));
            }
            
            $addProduct = $observer->getProduct();
            $product = null;
            if ($addProduct->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) 
            {
                $attributes = $postValues['super_attribute'];                
                // $this->logger->log('600', 'Selected attributes '.print_r($attributes, true));
                $product = $this->configurableproduct->getProductByAttributes($attributes, $addProduct);
            } else {
                $product = $addProduct;
            }
            $productQty = $this->productSourceAvailability->getQuantityInformationForProduct($product->getSku());
            $sourceList = [];
            foreach ($productQty as $pQty) {
                if ($pQty['source_code'] == $sources) {
                    $sourceList[] = $pQty;
                }
            }        

            if ($sourceList) {
                $inStock = 0;
                foreach ($sourceList as $qty) {
                    if ($qty['quantity'] == 0 || !$qty['status']) {
                        $inStock += 0;
                    } else {
                        $inStock += 1;
                    }
                }

                if ($inStock == 0) {
                    throw new LocalizedException(__('The requested qty is not available in selected store.'));
                }
            } else {
                throw new LocalizedException(__('The product is not available in the selected store.'));        
            }
        }

        return;
    }
}
