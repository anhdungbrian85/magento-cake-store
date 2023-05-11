<?php

namespace X247Commerce\AdminStaff\Block;

use Magento\Backend\Model\Auth\Session;
use Magento\Framework\View\Element\Template\Context;

class RoleData extends \Magento\Framework\View\Element\Template
{
    public function __construct(
        Context $context,
        Session $adminSession
    ) {
        parent::__construct($context);
        $this->adminSession = $adminSession;
    }

    public function isStaffUser() 
    {
        $roleData = $this->adminSession->getUser()->getRole()->getData();
        $userData = $this->adminSession->getUser()->getData();
        if($roleData['role_name'] == 'Staff') {
            return $roleData['role_name'];
        } else {
            return false;
        }
    }
}
