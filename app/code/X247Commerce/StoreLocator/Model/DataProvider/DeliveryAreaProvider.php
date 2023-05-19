<?php
namespace X247Commerce\StoreLocator\Model\DataProvider;

use X247Commerce\StoreLocator\Model\ResourceModel\DeliveryArea\CollectionFactory;
use Magento\Backend\Model\Auth\Session as AdminSession;
use X247Commerce\StoreLocatorSource\Model\ResourceModel\LocatorSourceResolver;
use X247Commerce\StoreLocatorSource\Helper\User as UserHelper;

class DeliveryAreaProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    protected $adminSession;
    protected $locatorSourceResolver;
    protected $userHelper;

    public function __construct(
        CollectionFactory $collectionFactory,
        AdminSession $adminSession,
        LocatorSourceResolver $locatorSourceResolver,
        UserHelper $userHelper,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $collection = $collectionFactory->create();
        $this->_adminSession = $adminSession;
        $this->locatorSourceResolver = $locatorSourceResolver;
        $this->userHelper = $userHelper;
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data
        );
        $this->collection = $collectionFactory->create()
                          ->addFieldToSelect('*');
    }

    /**
     * Return collection
     *
     * @return AbstractCollection
     */
    public function getCollection()
    {
        $staffRole = $this->userHelper->getStaffRole();
        $roleData = $this->_adminSession->getUser()->getRole()->getData();
        $userData = $this->_adminSession->getUser()->getData();

        $roleId = (int) $roleData['role_id'];
        
        if ($roleId == $staffRole) {
            $storeIds = $this->locatorSourceResolver->getAmLocatorStoresByUser($userData["user_id"]);
            $this->collection->addFieldToFilter('store_id', ['in' => $storeIds]);
        }
        return $this->collection;
    }
}