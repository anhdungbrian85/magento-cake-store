<?php
/**
 *
 */
namespace X247Commerce\DeliveryPopUp\Controller\Index;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\BlockFactory;
use WeltPixel\Quickview\Helper\Data;
use X247Commerce\Checkout\Api\StoreLocationContextInterface;
use Magento\Store\Model\StoreManagerInterface;

class SelectLocation extends \Amasty\Storelocator\Controller\Index\Ajax
{

    protected Cart $cart;
	protected CustomerSession $customerSession;
	protected JsonFactory $resultJsonFactory;
    protected StoreLocationContextInterface $storeLocationContextInterface;
    protected ProductRepositoryInterface $productRepository;
    protected BlockFactory $blockFactory;
    protected Data $_wpHelper;
    protected StoreManagerInterface $storeManager;

    public function __construct(
        StoreManagerInterface $storeManager,
        BlockFactory $blockFactory,
        Cart $cart,
        Context $context,
        CustomerSession $customerSession,
        JsonFactory $resultJsonFactory,
        StoreLocationContextInterface $storeLocationContextInterface,
        ProductRepositoryInterface $productRepository,
        Data $_wpHelper
    ) {
        parent::__construct($context);
        $this->blockFactory = $blockFactory;
        $this->cart = $cart;
        $this->customerSession = $customerSession;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->storeLocationContextInterface = $storeLocationContextInterface;
        $this->productRepository = $productRepository;
        $this->_wpHelper = $_wpHelper;
        $this->storeManager = $storeManager;
    }

    /**
     * @return Json|void
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function execute()
    {
    	$data = $this->getRequest()->getPostValue();
        $deliveryType = $data["delivery_type"];
        $resultJson = $this->resultJsonFactory->create();

        if (!empty($data) && !empty($data["location_id"])) {
            $locationId = $data["location_id"];
            $this->storeLocationContextInterface->setStoreLocationId($locationId);
            $this->storeLocationContextInterface->setDeliveryType($deliveryType);
            try {
                if ($data['is_product_page'] && !empty($data['product'])) {
                    $addToCartFormData = $data;
                    $productId = (int)$addToCartFormData['product'];
                    if ($productId) {
                        $storeId = $this->_objectManager->get(
                            StoreManagerInterface::class
                        )->getStore()->getId();
                        $product = $this->productRepository->getById($productId, false, $storeId);
                        if ($product) {
                            $this->cart->addProduct($product, $addToCartFormData);
                            $this->cart->save();
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
                return $resultJson->setData(['store_location_id' => $locationId]);
            }
            return $resultJson->setData(['store_location_id' => $locationId]);
        }
        return $resultJson->setData(
            [
                'store_location_id' => 0,
                'redirect_url' => $deliveryType == 2 ?
                    $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB) . 'celebration-cakes/click-collect-1-hour.html' :
                    null
            ]
        );
    }

    /**
     * @param $productId
     * @return string
     */
    private function getAjaxPopupContent($productId): string
    {
        if (!$this->_wpHelper->isAjaxCartEnabled()) {
            return '';
        }
        $abstractProductBlock = $this->blockFactory->createBlock('\Magento\Catalog\Block\Product\AbstractProduct');
        $confirmationPopupBlock = $this->blockFactory->createBlock('\WeltPixel\Quickview\Block\ConfirmationPopup')
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
