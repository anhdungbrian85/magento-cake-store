<?php

namespace X247Commerce\DeliveryPopUp\Controller\Index;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Context;
use Amasty\Storelocator\Model\ResourceModel\Location\Collection as LocationCollection;
use Amasty\Storelocator\Model\ResourceModel\Location\CollectionFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use X247Commerce\Checkout\Api\StoreLocationContextInterface;
use X247Commerce\StoreLocator\Helper\DeliveryArea as DeliveryAreaHelper;
use Magento\Store\Model\StoreManagerInterface;

class Ajax extends \Amasty\Storelocator\Controller\Index\Ajax
{

    protected CustomerSession $customerSession;
    protected CollectionFactory $locationCollectionFactory;
    protected JsonFactory $resultJsonFactory;
    protected StoreLocationContextInterface $storeLocationContextInterface;
    protected DeliveryAreaHelper $deliveryAreaHelper;
    protected StoreManagerInterface $storeManager;

    public function __construct(
        CustomerSession $customerSession,
        CollectionFactory $locationCollectionFactory,
        JsonFactory $resultJsonFactory,
        StoreLocationContextInterface $storeLocationContextInterface,
        DeliveryAreaHelper $deliveryAreaHelper,
        StoreManagerInterface $storeManager,
        Context $context
    )
    {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customerSession = $customerSession;
        $this->locationCollectionFactory = $locationCollectionFactory;
        $this->storeLocationContextInterface = $storeLocationContextInterface;
        $this->deliveryAreaHelper = $deliveryAreaHelper;
        $this->storeManager = $storeManager;
    }


    public function execute()
    {
        $deliveryType = $this->getRequest()->getPost('delivery-type');
        $this->storeLocationContextInterface->setDeliveryType($deliveryType);
        $destCode = $this->getRequest()->getParam('dest');
        $this->storeLocationContextInterface->setCustomerPostcode($destCode);
        $resultJson = $this->resultJsonFactory->create();
        if ($deliveryType == 1) {

            $location = $this->getClosestStoreLocation($destCode);
            if ($location && $location->getId()) {
                if ($location->getEnableDelivery() == 0) {
                    return $resultJson->setData(['enable_delivery' => 0]);
                }

                $this->storeLocationContextInterface->setStoreLocationId($location->getId());

                return $resultJson->setData(['store_location_id' => $location->getId()]);
            }   else {
                return $resultJson->setData(['delivery_status' => false]);
            }
        } else {
            $this->getCloseStoreLocations();
        }
    }

    public function getCloseStoreLocations()
    {
        $this->_view->loadLayout();
        $lng = $this->getRequest()->getPost('lng');
        $lat = $this->getRequest()->getPost('lat');

        /** @var \Amasty\Storelocator\Block\Location $block */
        $block = $this->_view->getLayout()->getBlock('amlocator_ajax');
        $block->setData('lng', $lng);
        $block->setData('lat', $lat);
        return $this->getResponse()->setBody($block->getJsonLocations());
    }

    public function getClosestStoreLocation($postcode)
    {
        if (!$postcode) {
            return false;
        }
        $needToPrepareCollection = false;
        $location = $this->locationCollectionFactory->create()->addFieldToFilter('enable_delivery', ['eq' => 1]);
        $deliverLocations = $this->deliveryAreaHelper->getDeliverLocations($postcode);
        $deliverLocationsIds = [];

        foreach ($deliverLocations as $deliverLocation) {
            $deliverLocationsIds[] = $deliverLocation->getStoreId();
        }

        $location->addFieldtoFilter('id', ['in' => $deliverLocationsIds]);
        $location->applyDefaultFilters();
        return $location->getFirstItem();
    }
}
