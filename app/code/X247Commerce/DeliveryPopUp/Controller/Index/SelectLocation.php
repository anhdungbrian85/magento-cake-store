<?php

namespace X247Commerce\DeliveryPopUp\Controller\Index;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\NoSuchEntityException;
use X247Commerce\Checkout\Api\StoreLocationContextInterface;
use Magento\Checkout\Model\Cart as CustomerCart;

class SelectLocation extends \Amasty\Storelocator\Controller\Index\Ajax
{

    protected CustomerCart $cart;

	protected CustomerSession $customerSession;

	protected JsonFactory $resultJsonFactory;

    protected StoreLocationContextInterface $storeLocationContextInterface;

    protected ProductRepositoryInterface $productRepository;

	public function __construct(
        CustomerCart $cart,
        \Magento\Framework\App\Action\Context $context,
        CustomerSession $customerSession,
        JsonFactory $resultJsonFactory,
        StoreLocationContextInterface $storeLocationContextInterface,
        ProductRepositoryInterface $productRepository
    ) {
        parent::__construct($context);
        $this->cart = $cart;
        $this->customerSession = $customerSession;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->storeLocationContextInterface = $storeLocationContextInterface;
        $this->productRepository = $productRepository;
    }

    public function execute()
    {
    	$data = $this->getRequest()->getPostValue();
        if (!empty($data["location_id"])) {
            $locationId = $data["location_id"];
            $deliveryType = $data["delivery_type"];
            $this->storeLocationContextInterface->setStoreLocationId($locationId);
            $this->storeLocationContextInterface->setDeliveryType($deliveryType);
            $resultJson = $this->resultJsonFactory->create();
            return $resultJson->setData(['store_location_id' => $locationId]);
        }
        if ($data['is_product_page']) {
            if (!empty($data['add_to_cart_form_data'])) {
                $addToCartFormDataStr = urldecode($data['add_to_cart_form_data']);
                parse_str($addToCartFormDataStr, $addToCartFormData);
                $productId = (int)$addToCartFormData['product'];
                if ($productId) {
                    $storeId = $this->_objectManager->get(
                        \Magento\Store\Model\StoreManagerInterface::class
                    )->getStore()->getId();
                    $product = $this->productRepository->getById($productId, false, $storeId);
                    $this->cart->addProduct($product, $addToCartFormData);
                    if (!empty($related)) {
                        $this->cart->addProductsByIds(explode(',', $related));
                    }
                }

            }
        }
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData([
                                    'store_location_id' => 0,
                                    'redirect_url' => $deliveryType == 2 ? $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB) . 'celebration-cakes/click-collect-1-hour.html'  : null
                                ]);

    }
}
