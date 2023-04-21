<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace X247Commerce\StoreLocatorSource\Helper;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Backend\Model\Auth\Session as AdminSession;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\Context;
use X247Commerce\StoreLocatorSource\Model\ResourceModel\LocatorSourceResolver;

/**
 * Sales admin helper.
 */
class User extends AbstractHelper
{
    const XML_CONFIG_PATH_STAFF_ROLE_ID = 'yext/staff_role/role_id';

    protected AdminSession $adminSession;
    protected LocatorSourceResolver $locatorSourceResolver;

    public function __construct(
        Context $context,
        AdminSession $adminSession,
        LocatorSourceResolver $locatorSourceResolver
    ) {
        parent::__construct($context);
        $this->adminSession = $adminSession;
        $this->locatorSourceResolver = $locatorSourceResolver;
    }

    public function getCurrentUser()
    {
        return $this->adminSession->getUser();
    }

    public function getStaffRole() 
    {
        return  $this->scopeConfig->getValue(self::XML_CONFIG_PATH_STAFF_ROLE_ID, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Check current admin user is staff of not
     * @return bool
     * */
    public function isStaffUser($user = null) 
    {
        if (!$user) {
            $user = $this->getCurrentUser();
        }
        if ($user) {
            $staffRole = $this->getStaffRole();
            $currentAdminRole = $user->getRole()->getId();
            return $staffRole == $currentAdminRole;
        }
        return false;
    }

    /**
     * Check an admin user can manage order or not
     * @return bool
     * */
    public function userCanManageOrder($order, $user = null) 
    {   
        if (!$user) {
            $user = $this->getCurrentUser();
        }
        $userAmLocatorStores = $this->locatorSourceResolver->getAmLocatorStoresByUser($user);
        $orderStoreLocation = $order->getData('store_location_id');
        $isStaffUser = $this->isStaffUser($user);
        if (!$isStaffUser || (!empty($orderStoreLocation) && in_array($orderStoreLocation, $userAmLocatorStores))) {
            return true;
        }
        return false;
    }
    
    /**
     * Check an admin user can manage invoice or not
     * @return bool
     * */
    public function userCanManageInvoice($invoice, $user = null) 
    {
        return $this->userCanManageOrder($invoice->getOrder(), $user);
    }

    /**
     * Check an admin user can manage shipment or not
     * @return bool
     * */
    public function userCanManageShipment($shipment, $user = null) 
    {
        return $this->userCanManageOrder($shipment->getOrder(), $user);
    }

    /**
     * Check an admin user can manage credit memo or not
     * @return bool
     * */
    public function userCanManageCreditMemo($creditMemo, $user = null) 
    {
        return $this->userCanManageOrder($creditMemo->getOrder(), $user);
    }

}
