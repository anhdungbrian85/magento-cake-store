<?php

namespace X247Commerce\DeliveryPopUp\Controller\Index;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\NoSuchEntityException;
use X247Commerce\Checkout\Api\StoreLocationContextInterface;
use \Magento\Store\Model\StoreManagerInterface;

class SelectLocation extends \Amasty\Storelocator\Controller\Index\Ajax
{

    protected \Magento\Checkout\Model\Cart $cart;

	protected CustomerSession $customerSession;

	protected JsonFactory $resultJsonFactory;

    protected StoreLocationContextInterface $storeLocationContextInterface;

    protected ProductRepositoryInterface $productRepository;

    protected \Magento\Framework\View\Element\BlockFactory $_blockFactory;

    protected \WeltPixel\Quickview\Helper\Data $_wpHelper;

    protected StoreManagerInterface $storeManager;

	public function __construct(
        StoreManagerInterface $storeManager,
        \Magento\Framework\View\Element\BlockFactory $_blockFactory,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\App\Action\Context $context,
        CustomerSession $customerSession,
        JsonFactory $resultJsonFactory,
        StoreLocationContextInterface $storeLocationContextInterface,
        ProductRepositoryInterface $productRepository,
        \WeltPixel\Quickview\Helper\Data $_wpHelper
    ) {
        parent::__construct($context);
        $this->_blockFactory = $_blockFactory;
        $this->cart = $cart;
        $this->customerSession = $customerSession;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->storeLocationContextInterface = $storeLocationContextInterface;
        $this->productRepository = $productRepository;
        $this->_wpHelper = $_wpHelper;
        $this->storeManager = $storeManager;
    }

    public function execute()
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/popup_add_to_cart.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('Starting debug');
    	$data = $this->getRequest()->getPostValue();
        $deliveryType = $data["delivery_type"];
        $resultJson = $this->resultJsonFactory->create();

        if (!empty($data["location_id"])) {
            $locationId = $data["location_id"];
            $this->storeLocationContextInterface->setStoreLocationId($locationId);
            $this->storeLocationContextInterface->setDeliveryType($deliveryType);
            try {
                if ($data['is_product_page']) {
                    if (!empty($data['add_to_cart_form_data'])) {
                        $addToCartFormDataStr = urldecode($data['add_to_cart_form_data']);
                        parse_str($addToCartFormDataStr, $addToCartFormData);
                        $productId = (int)$addToCartFormData['product'];
                        if ($productId) {
                            $storeId = $this->_objectManager->get(
                                StoreManagerInterface::class
                            )->getStore()->getId();
                            $product = $this->productRepository->getById($productId, false, $storeId);
                            if ($product) {
                                $this->cart->addProduct($product, $addToCartFormData);
                                if (!empty($related)) {
                                    $this->cart->addProductsByIds(explode(',', $related));
                                }
                                $this->cart->save();
                            }
                        }
                        $resultPopupContent = $this->getAjaxPopupContent($product->getId());
                        if ($resultPopupContent) {
                            return $resultJson->setData(
                                [
                                    'store_location_id' => $locationId,
                                    'confirmation_popup_content' => $resultPopupContent
                                ]
                            );
                        }
                    }
                }
            } catch (\Exception $e) {
                $logger->info('Error:'. $e->getMessage());
                return $resultJson->setData(['store_location_id' => $locationId]);
            }
            return $resultJson->setData(['store_location_id' => $locationId]);
        }
        return $resultJson->setData(
            [
                'store_location_id' => 0,
                'redirect_url' => $deliveryType == 2 ? $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB) . 'celebration-cakes/click-collect-1-hour.html'  : null
            ]
        );
    }

    private function getAjaxPopupContent($productId)
    {
        if (!$this->_wpHelper->isAjaxCartEnabled()) {
            return null;
        }
        $abstractProductBlock = $this->_blockFactory->createBlock('\Magento\Catalog\Block\Product\AbstractProduct');
        $confirmationPopupBlock = $this->_blockFactory->createBlock('\WeltPixel\Quickview\Block\ConfirmationPopup')
            ->setTemplate('WeltPixel_Quickview::confirmation_popup/content.phtml')
            ->setProductViewModel($abstractProductBlock)
            ->setLastAddedProductId($productId);

        /** @var \Magento\Framework\Pricing\Render $priceRender */
        $priceRender = $confirmationPopupBlock->getLayout()->getBlock('product.price.render.default');
        if (!$priceRender) {
            $confirmationPopupBlock->getLayout()->createBlock(
                \Magento\Framework\Pricing\Render::class,
                'product.price.render.default',
                ['data' => ['price_render_handle' => 'catalog_product_prices']]
            );
        }

        return $confirmationPopupBlock->toHtml();
    }
}
