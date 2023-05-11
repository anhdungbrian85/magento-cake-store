<?php

namespace X247Commerce\StoreLocator\UiComponent\Form;

use Magento\Framework\Option\ArrayInterface;

class StoreList implements ArrayInterface
{
    protected $locationCollectionFactory;
    protected $adminSession;
    protected $locatorSourceResolver;

    public function __construct(
        \Amasty\Storelocator\Model\ResourceModel\Location\CollectionFactory $locationCollectionFactory,
        \Magento\Backend\Model\Auth\Session $adminSession,
        \X247Commerce\StoreLocatorSource\Model\ResourceModel\LocatorSourceResolver $locatorSourceResolver
    )
    {
        $this->locationCollectionFactory = $locationCollectionFactory;
        $this->adminSession = $adminSession;
        $this->locatorSourceResolver = $locatorSourceResolver;
    }


    private function getStoreCollection()
    {
        $roleData = $this->adminSession->getUser()->getRole()->getData();
        $userData = $this->adminSession->getUser()->getData();

        $roleId = (int) $roleData['role_id'];
        $userStore = [];

        if ($roleId != 1) {
            $userStore = $this->getAssignStore($userData['user_id']);

            $collection = $this->locationCollectionFactory->create()->addFieldToFilter('id',  ['in' => $userStore]);
            // var_dump($collection->getSelect()->__toString());die();
        } else {
            $collection = $this->locationCollectionFactory->create();
        }
        return $collection;
    }
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $locationArr = [];
        $collection = $this->getStoreCollection();

        foreach ($collection as $item) {
            $locationArr[] = [
                'value' => $item->getId(), 'label' => $item->getName()
            ];
        }

        return $locationArr;
    }

    public function toArray()
    {
        $locationArr = [];
        $collection = $this->getStoreCollection();
        foreach ($collection as $item) {
            $locationArr[$item->getId()] = $item->getName();
        }
        return $locationArr;
    }

    public function getAssignStore($userId)
    {
        $userStore = $this->locatorSourceResolver->getAmLocatorStoresByUser($userId);

        return $userStore;
    }
}
