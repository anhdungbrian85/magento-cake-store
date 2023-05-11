<?php

namespace X247Commerce\Checkout\Controller\Shipping;

use Magento\Checkout\Controller\Checkout;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Checkout\Model\Session;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Amasty\Storelocator\Model\LocationFactory;
use Amasty\Storelocator\Model\ResourceModel\Location\Collection as LocationCollection;
use Amasty\Storelocator\Model\ResourceModel\Location\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Store\Model\StoreManagerInterface;
use X247Commerce\Checkout\Api\StoreLocationContextInterface;
use X247Commerce\StoreLocator\Helper\DeliveryArea;

class ValidateDeliveryPostcode extends Action
{

    protected StoreLocationContextInterface $storeLocationContext;
    protected LocationFactory $locationFactory;
    protected DeliveryArea $deliveryAreaHelper;
    protected JsonFactory $resultJsonFactory;

    public function __construct(
        Context $context,
        StoreLocationContextInterface $storeLocationContext,
        LocationFactory $locationFactory,
        DeliveryArea $deliveryAreaHelper,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->storeLocationContext = $storeLocationContext;
        $this->locationFactory = $locationFactory;
        $this->deliveryAreaHelper = $deliveryAreaHelper;
        $this->resultJsonFactory = $resultJsonFactory;
    }


    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $postcode = $this->getRequest()->getParam('postcode');
        $storeLocationId = $this->getRequest()->getParam('store_location_id');
        if (!$postcode) {
            return $resultJson->setData(
                ['status' => -1]
            );
        }
        $wlAreaCollection = $this->deliveryAreaHelper->getDeliverLocations($postcode);
        if (!$wlAreaCollection->count()) {
            return $resultJson->setData(
                [
                    'status' => false,
                    'message' => __('We do not yet deliver to that area. Please arrange to collect in-store or use another delivery address!')
                ]
            );
        }

        return $resultJson->setData(
            [
                'status' => true
            ]
        );
    }
}
