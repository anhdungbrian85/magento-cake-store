<?php

namespace X247Commerce\StoreLocator\Plugin;

use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider;
use Magento\Backend\Model\Auth\Session as AdminSession;
use X247Commerce\StoreLocatorSource\Model\ResourceModel\LocatorSourceResolver;
use X247Commerce\StoreLocatorSource\Helper\User as UserHelper;
use Magento\Framework\Api\Search\SearchResultInterface;

class DataProviderPlugin
{
    protected $adminSession;
    protected $locatorSourceResolver;
    protected $userHelper;

    public function __construct(
        AdminSession $adminSession,
        LocatorSourceResolver $locatorSourceResolver,
        UserHelper $userHelper
    ) {
        $this->adminSession = $adminSession;
        $this->locatorSourceResolver = $locatorSourceResolver;
        $this->userHelper = $userHelper;
    }

    public function aroundGetSearchResult(DataProvider $subject, callable $proceed)
    {
        // Get the search result from the original method
        /** @var SearchResultInterface $searchResult */
        $searchResult = $proceed();

        // Get user role and user data
        $staffRole = $this->userHelper->getStaffRole();
        $roleData = $this->adminSession->getUser()->getRole()->getData();
        $userData = $this->adminSession->getUser()->getData();

        // Get the role ID
        $roleId = (int) $roleData['role_id'];

        // If the role ID matches the staff role, filter the search result
        if ($roleId == $staffRole) {
            $storeIds = $this->locatorSourceResolver->getAmLocatorStoresByUser($userData["user_id"]);
            $searchResult->addFieldToFilter('store_id', ['in' => $storeIds]);
        }

        return $searchResult;
    }
}
