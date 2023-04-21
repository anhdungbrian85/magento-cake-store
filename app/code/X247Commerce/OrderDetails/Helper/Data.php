<?php
namespace X247Commerce\OrderDetails\Helper;

use Magento\CatalogInventory\Api\StockRegistryInterfaceFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {

    private $httpContext;
    protected $productRepository;
    protected $_session;
    protected $price_helper;
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Customer\Model\Session $session
     * @param \Magento\Framework\Pricing\Helper\Data
     */
    public function __construct(
    \Magento\Framework\App\Helper\Context $context,
    \Magento\Catalog\Model\ProductRepository $productRepository,
    \Magento\Framework\App\Http\Context $httpContext,
    StockRegistryInterfaceFactory $stockRegistry,
    \Magento\Customer\Model\Session $session,
    \Magento\Framework\Pricing\Helper\Data $price_helper
    ) {
        $this->productRepository = $productRepository;
        $this->httpContext = $httpContext;
        $this->_session = $session;
        $this->stockRegistry = $stockRegistry;
        $this->price_helper=$price_helper;
        parent::__construct($context);
    }

    /**
     * Load product from productId
     * @param int $id Product id
     * @return $this
     */
    public function getProductById($id) {
        return $this->productRepository->getById($id);
    }

    /**
     * Check Customer is login or not
     * @return boolean
     */
    public function isLoggedIn() {
        $isLoggedIn = $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
        return $isLoggedIn;
    }

    /**
     * Get Formated Price
     * @param fload price 
     * @return boolean
    */
    public function getFormatedPrice($price='')
    {
        return $this->price_helper->currency($price, true, false);
    }

    /**
     * @param ProductInterface|string $product
     * @param OrderItemInterface|null $item
     *
     * @return ProductInterface
     */
    public function loadUsedProduct($product, $item)
    {
        if ($product->getTypeId() == Configurable::TYPE_CODE) {
            $product = $this->getSimpleProductFromOrderItem($product, $item);
        }

        return $this->getProductById($product->getId());
    }

    /**
     * @param ProductInterface        $product
     * @param OrderItemInterface|null $item
     *
     * @return ProductInterface
     */
    private function getSimpleProductFromOrderItem($product, $item)
    {
        if (!is_null($item) && $item->getOptionByCode('simple_product')) {
            $product = $item->getOptionByCode('simple_product')->getProduct();
        }

        return $product;
    }

    /**
     * @param ProductInterface $product
     *
     * @return StockItemInterface
     */
    public function getStockItemForUsedProduct($product)
    {
        return $this->stockRegistry->create()->getStockItem($product->getId());
    }

}
