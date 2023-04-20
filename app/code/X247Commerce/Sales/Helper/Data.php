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

/**
 * Sales admin helper.
 */
class Data extends AbstractHelper
{
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
        $this->adminSession = $adminSession;
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
     * 
     */
    public function isStaffUser($user = null) 
    {
        if (!$user) {
            $user = $this->getCurrentUser();
        }
        $staffRole = $this->getStaffRole();
        $user = $this->getCurrentUser();
        $currentAdminRole = $user->getRole()->getId();
        return $staffRole == $currentAdminRole;
    }

    /**
     * Check an admin user can manage order or not
     * @return bool
     * 
     */
    public function checkUserCanManageOrder($order, $user = null)
    {
        if (!$user) {
            $user = $this->getCurrentUser();
        }

    }
}
