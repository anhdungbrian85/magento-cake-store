<?php

namespace X247Commerce\DeliveryPopUp\Controller\Index;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Context;
use Amasty\Storelocator\Model\ResourceModel\Location\Collection as LocationCollection;
use Amasty\Storelocator\Model\ResourceModel\Location\CollectionFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use X247Commerce\Checkout\Api\StoreLocationContextInterface;

class Ajax extends \Amasty\Storelocator\Controller\Index\Ajax
{

    protected CustomerSession $customerSession;
    protected CollectionFactory $locationCollectionFactory;
    protected JsonFactory $resultJsonFactory;
    protected StoreLocationContextInterface $storeLocationContextInterface;
    
    public function __construct(
        CustomerSession $customerSession,
        CollectionFactory $locationCollectionFactory,
        JsonFactory $resultJsonFactory,
        StoreLocationContextInterface $storeLocationContextInterface,
        Context $context,

    )
    {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customerSession = $customerSession;
        $this->locationCollectionFactory = $locationCollectionFactory;
        $this->storeLocationContextInterface = $storeLocationContextInterface;
    }

    /**
     * Default customer account page
     *
     * @return void
     */
    public function execute()
    {
        if ($this->getRequest()->getPost('delivery-type') == 0) {
            return parent::execute();
        }   else {
            $location = $this->getClosestStoreLocation();
            if ($location->getId()) {

                $this->customerSession->setStoreLocationId($location->getId());
                $this->storeLocationContextInterface->setStoreLocationId($location->getId());

                $resultJson = $this->resultJsonFactory->create();
                return $resultJson->setData(['store_location_id' => $location->getId()]);
            }   else {
                return parent::execute();
            }
        }
    }

    public function getClosestStoreLocation()
    {
        $needToPrepareCollection = false;
        $location = $this->locationCollectionFactory->create();
        $location->applyDefaultFilters();
        return $location->getFirstItem();
    
    }
}
