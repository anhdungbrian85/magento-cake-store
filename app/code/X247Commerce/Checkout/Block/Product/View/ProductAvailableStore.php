<?php
namespace X247Commerce\Checkout\Block\Product\View;

use Magento\Catalog\Model\Product;

class ProductAvailableStore extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Product
     */
    protected $_product = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    protected $_stockItemRepository;
    protected $_checkoutHelper;
    protected $locatorSourceResolver;
    protected $customerSession;
    protected $storeLocationContext;
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository,
        \X247Commerce\Checkout\Helper\Data $checkoutHelper,
        \X247Commerce\StoreLocatorSource\Model\ResourceModel\LocatorSourceResolver $locatorSourceResolver,
        \Magento\Customer\Model\Session $customerSession,
        \X247Commerce\Checkout\Api\StoreLocationContextInterface $storeLocationContext,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_stockItemRepository = $stockItemRepository;
        $this->_checkoutHelper = $checkoutHelper;
        $this->locatorSourceResolver = $locatorSourceResolver;
        $this->customerSession = $customerSession;
        $this->storeLocationContext = $storeLocationContext;
        parent::__construct($context, $data);
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        if (!$this->_product) {
            $this->_product = $this->_coreRegistry->registry('product');
        }
        
        return $this->_product;
    }

    // public function getStockItem($productId)
    // {
    //     return $this->_stockItemRepository->get($productId);
    // }
    /**
     * @param $productSku
     * @return array
     */
    public function getQuantityInformationForProduct($productSku)
    {
        return $this->_checkoutHelper->getQuantityInformationForProduct($productSku);
    }

    public function getAmLocatorBySource($sourceCode)
    {
        $storeIds = [];
        foreach ($this->locatorSourceResolver->getAmLocatorBySource($sourceCode) as $locationIds) {
            $storeIds[] = $locationIds;
        }
        return $storeIds;
    }

    public function getAmLocationByLocationId($id)
    {
        return $this->_checkoutHelper->getAmLocationByLocationId($id);
    }
    // public function getAvailableSourceOfProduct($stockId, $productSku)
    // {
    //     return $this->_checkoutHelper->getAvailableSourceOfProduct($stockId, $productSku);
    // }
    public function getCustomerSession()
    {
        return $this->customerSession;
    }
    public function getError()
    {
       return $this->customerSession->getStoreLocationId();
    }

    public function getStoreLocationIdByContext()
    {
        return $this->storeLocationContext->getStoreLocationId();
    }
    public function setStoreLocationIdByContext($locationId)
    {
        return $this->storeLocationContext->setStoreLocationId($locationId);
    }
    /**
     * check product available in current store location
     *
     * @return bool
     */
    public function checkProductAvailableInStore($locationId, $productSku)
    {
        return $this->locatorSourceResolver->checkProductAvailableInStore($locationId, $productSku);
    }
}
