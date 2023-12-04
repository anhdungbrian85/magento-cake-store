<?php
/**
 * Router for Seo Module.
 *
 * @author    Zakaria KLIOUEL <zakli@smile.fr>
 * @copyright 2018 Smile
 */

namespace X247Commerce\SpecialOffer\Controller;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Session;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable as ConfigurableResource;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Redirect;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\RouterInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use X247Commerce\Checkout\Api\StoreLocationContextInterface;
use X247Commerce\SpecialOffer\Helper\Data as Helper;
use X247Commerce\StoreLocatorSource\Model\ResourceModel\LocatorSourceResolver;
use Magento\Framework\Message\ManagerInterface;

class DispatchAddToCartRouter implements RouterInterface
{
//    protected Helper $helper;
//    protected ActionFactory $actionFactory;
//    protected ResponseInterface $response;
//
//    public function __construct(
//        Helper $helper,
//        ActionFactory $actionFactory,
//        ResponseInterface $response
//
//    ) {
//        $this->helper = $helper;
//        $this->actionFactory = $actionFactory;
//        $this->response = $response;
//    }
    protected $checkoutSession;
    protected $cartRepository;
    protected $productRepository;
    protected $resultFactory;
    protected $cart;
    protected $helper;
    protected $configurableResource;
    protected $productAttributeRepository;
    protected $storeLocationContextInterface;
    protected $resouceConnection;
    protected $locatorSourceResolver;
    protected ResponseInterface $response;
    protected ActionFactory $actionFactory;
    protected $messageManager;

    public function __construct(
        Context                    $context,
        Session                    $checkoutSession,
        CartRepositoryInterface    $cartRepository,
        ProductRepositoryInterface $productRepository,
        ResultFactory              $resultFactory,
        Cart                       $cart,
        Helper                     $helper,
        ConfigurableResource       $configurableResource,
        ProductAttributeRepositoryInterface $productAttributeRepository,
        StoreLocationContextInterface $storeLocationContextInterface,
        ResourceConnection $resourceConnection,
        LocatorSourceResolver $locatorSourceResolver,
        ResponseInterface $response,
        ActionFactory $actionFactory,
        ManagerInterface $messageManager

    ) {
        $this->checkoutSession = $checkoutSession;
        $this->cartRepository = $cartRepository;
        $this->productRepository = $productRepository;
        $this->resultFactory = $resultFactory;
        $this->cart = $cart;
        $this->helper = $helper;
        $this->configurableResource = $configurableResource;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->storeLocationContextInterface = $storeLocationContextInterface;
        $this->resouceConnection = $resourceConnection;
        $this->locatorSourceResolver = $locatorSourceResolver;
        $this->response = $response;
        $this->actionFactory = $actionFactory;
        $this->messageManager = $messageManager;
    }

    public function match(RequestInterface $request)
    {
        $enable = $this->helper->isEnable();
        $coupon = $this->helper->getSpecialCoupon();
        if (!$enable || !$coupon) {
            return null;
        }
        $path = $request->getOriginalPathInfo();
        $path = trim($path, '/');
        if (strtolower($coupon) == strtolower($path)) {
            $redirectUrl = '/';
            $this->response->setRedirect($redirectUrl);
            $redirectUrl = $this->addToCart($coupon);
            return $this->actionFactory->create(Redirect::class);

        }
        return null;
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function addToCart($coupon)
    {
        try {
            $redirectUrl = '/';
            $sku = $this->helper->getOfferProductSku();
            $quote = $this->checkoutSession->getQuote();

            if (empty($sku) || empty($coupon)) {
                return $redirectUrl;
            }

            if ($quote->getCouponCode() && strtolower($quote->getCouponCode()) == strtolower($coupon)) {
                $this->messageManager->addErrorMessage(__('Please remove current coupon code to claim this url!'));
                return $redirectUrl;
            }

            $product = $this->productRepository->get($sku);
            $origProduct = $product;
            if ($product->getTypeId() !== 'simple') {
                $this->messageManager->addErrorMessage(__('We cannot found your product for now!'));
                return $redirectUrl;
            }

            $params = [];

            if ($product->getVisibility() == 1) {
                // not visible individual
                $parentProducts = $this->configurableResource->getParentIdsByChild($product->getId());
                $child = $product;
                $product = $this->productRepository->getById($parentProducts[0]);
                $superAttribute = $this->getChildSuperAttribute($product, $child);
                $params['product'] = $product->getId();
                $params['super_attribute'] = $superAttribute;
            }

            $this->setLocationContext($origProduct); // Set any location
            $this->cart->addProduct($product, $params);
            $this->cart->save();

//            /**
//             * @todo remove wishlist observer \Magento\Wishlist\Observer\AddToCart
//             */
//            $this->_eventManager->dispatch(
//                'checkout_cart_add_product_complete',
//                ['product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
//            );
            $quote->setCouponCode($coupon)->collectTotals();
            $this->messageManager->addSuccessMessage(__('Your free cupcake has been added to your basket!'));
            $this->messageManager->addSuccessMessage(__('The coupon code '.$coupon.' has been applied!'));

            $this->cartRepository->save($quote);
            $redirectUrl = '/?applied='. base64_encode($coupon);

            return $redirectUrl;

        }  catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }  catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Cannot add to cart this product!'));
        }
        return $redirectUrl;
    }

    /**
     * @param \Magento\Catalog\Model\Product $parent
     * @param \Magento\Catalog\Model\Product $child
     * @return array
     */
    private function getChildSuperAttribute($parent, $child)
    {
        $_attributes = $parent->getTypeInstance(true)->getConfigurableAttributes($parent);
        $attributesPair = [];
        foreach ($_attributes as $_attribute) {
            $attributeId = (int)$_attribute->getAttributeId();
            $attributeCode = $this->getAttributeCode($attributeId);
            $attributesPair[$attributeId] = (int)$child->getData($attributeCode);
        }
        return $attributesPair;
    }

    /**
     * Get attribute code by attribute id
     * @param int $id
     * @return string
     */
    private function getAttributeCode(int $id)
    {
        return $this->productAttributeRepository->get($id)->getAttributeCode();
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    private function setLocationContext($product)
    {
        if (!$this->storeLocationContextInterface->getStoreLocationId()) {
            $connection = $this->resouceConnection->getConnection();
            $amLocationTbl = $this->resouceConnection->getTableName('amasty_amlocator_location');
            $linkSourceTbl = $this->resouceConnection->getTableName('amasty_amlocator_location_source_link');
            $inventory = $this->resouceConnection->getTableName('inventory_source_item');

            $q = $connection->select()->from(['m' => $amLocationTbl], 'id')
                ->joinLeft(['l' => $linkSourceTbl], "l.location_id = m.id", [])
                ->joinLeft(['i' => $inventory], "i.source_code = l.source_code", [])
                ->where('m.status = 1')
                ->where('m.enable_delivery = 1')
                ->where('m.curbside_enabled = 1')
                ->where('i.sku = \''.$product->getSku().'\'')
                ->where('i.status = 1')
                ->limit(1);

            $firstActiveLocation = $connection->fetchOne($q);
            $this->storeLocationContextInterface->setStoreLocationId($firstActiveLocation);
        }

        if (!$this->storeLocationContextInterface->getDeliveryType()) {
            $this->storeLocationContextInterface->setDeliveryType(StoreLocationContextInterface::FREE_COLLECTION_TYPE_VALUE);
        }
    }


}
