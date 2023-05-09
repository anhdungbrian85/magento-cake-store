<?php

namespace X247Commerce\Checkout\Observer\Cart;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use X247Commerce\StoreLocatorSource\Model\ResourceModel\LocatorSourceResolver;
use X247Commerce\Checkout\Api\StoreLocationContextInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use \Magento\Catalog\Api\CategoryListInterface;
use Magento\Eav\Api\AttributeSetRepositoryInterface;


class ValidateAddToCart implements ObserverInterface
{
    protected $customerSession;
    protected $_messageManager;
    protected $logger;
    protected $request;
    protected $product;
    protected $configurableproduct;
    protected $locatorSourceResolver;
    protected $checkoutSession;
    protected $storeLocationContext;
    protected $categoryCollection;
    protected $attributeSet;

    public function __construct(
        CustomerSession $customerSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        LoggerInterface $logger,
        RequestInterface $request,
        Product $product,
        Configurable $configurableproduct,
        CheckoutSession $checkoutSession,
        StoreLocationContextInterface $storeLocationContext,
        CategoryCollectionFactory $categoryCollection,
        AttributeSetRepositoryInterface $attributeSet,
    ) {
        $this->customerSession = $customerSession;
        $this->_messageManager = $messageManager;
        $this->logger = $logger;
        $this->request = $request;
        $this->product = $product;
        $this->configurableproduct = $configurableproduct;
        $this->checkoutSession = $checkoutSession;
        $this->storeLocationContext = $storeLocationContext;
        $this->categoryCollection = $categoryCollection;
        $this->attributeSet = $attributeSet;
    }

    public function execute(EventObserver $observer)
    {
        $deliveryType = $this->storeLocationContext->getDeliveryType();
        $postValues = $this->request->getPostValue();
        $addProduct = $observer->getProduct();

        if ($deliveryType == 2) {
            $product = null;
            if ($addProduct->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE)
            {
                $attributes = $postValues['super_attribute'];

                $product = $this->configurableproduct->getProductByAttributes($attributes, $addProduct);
            } else {
                $product = $addProduct;
            }

            // $this->logger->log('600', 'Selected attributes '.print_r($product->getAttributeSetId(), true));

            if ($product->getLeadDelivery() > 1) {
                $this->storeLocationContext->setDeliveryType(0);
                $this->checkoutSession->setDeliveryType(0);
            }
        }
        return;
    }
    public function getCategoryByUrlKey($urlKey)
    {
        $category = $this->categoryCollection
                                ->create()
                                ->addAttributeToFilter('url_key', $urlKey)
                                ->getFirstItem();
        return $category;
    }
}
