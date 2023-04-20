<?php

namespace X247Commerce\DeliveryPopUp\Controller\Index;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Customer\Model\Session as CustomerSession;
use X247Commerce\Checkout\Api\StoreLocationContextInterface;

class SelectLocation extends \Amasty\Storelocator\Controller\Index\Ajax
{
	protected CustomerSession $customerSession;
	protected JsonFactory $resultJsonFactory;
    protected StoreLocationContextInterface $storeLocationContextInterface;

	public function __construct(
        \Magento\Framework\App\Action\Context $context,
        CustomerSession $customerSession,
        JsonFactory $resultJsonFactory,
        StoreLocationContextInterface $storeLocationContextInterface
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->storeLocationContextInterface = $storeLocationContextInterface;
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
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData([
                                    'store_location_id' => 0,
                                    'redirect_url' => $deliveryType == 2 ? $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB) . 'celebration-cakes/click-collect-1-hour.html'  : null
                                ]);

    }
}
