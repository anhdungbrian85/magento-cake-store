<?php

namespace X247Commerce\DeliveryPopUp\Controller\Index;
use X247Commerce\Checkout\Api\StoreLocationContextInterface;
use Magento\Framework\App\Action\Context;
use Amasty\Storelocator\Model\ResourceModel\Location\Collection as LocationCollection;
use Amasty\Storelocator\Model\ResourceModel\Location\CollectionFactory;
use Magento\Framework\Controller\Result\JsonFactory;

class Ajax extends \Amasty\Storelocator\Controller\Index\Ajax
{

    protected StoreLocationContextInterface $storeLocationContextInterface;
    protected CollectionFactory $locationCollectionFactory;
    protected JsonFactory $resultJsonFactory;

    public function __construct(
        StoreLocationContextInterface $storeLocationContextInterface,
        CollectionFactory $locationCollectionFactory,
        JsonFactory $resultJsonFactory,
        Context $context,

    )
    {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->storeLocationContextInterface = $storeLocationContextInterface;
        $this->locationCollectionFactory = $locationCollectionFactory;
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
