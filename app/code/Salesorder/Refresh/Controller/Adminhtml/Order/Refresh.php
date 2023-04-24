<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Salesorder\Refresh\Controller\Adminhtml\Order;

class Refresh extends \Magento\Sales\Controller\Adminhtml\Order\Index
{
    /**
     * Orders grid
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /* Create a new product object */
        $_authSession = $objectManager->create('\Magento\Backend\Model\Auth\Session');
        $user = $_authSession->getUser();
        $loggedUserId =  $user->getId();
        if ($loggedUserId && $user->getRole()->getRoleName() == "Staff") {
            $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface'); 
            $baseurl = $storeManager->getStore()->getBaseUrl();

            //$page = $_SERVER['PHP_SELF'];
            //$page = 'https://staging-wwwfpgtvdjwlm-16.247docker.com/ecb-admin/sales/order';
            $page = $baseurl."ecb-admin/sales/order";
            $sec = "60";

            echo "<html>";
            echo" <head>";
            echo " <meta http-equiv='refresh' content=".$sec.';URL='.$page.">";
            echo " </head>";
            echo "</html>";
            $resultPage = $this->_initAction();
            $resultPage->getConfig()->getTitle()->prepend(__('Orders'));
            return $resultPage;
        }else{
            $resultPage = $this->_initAction();
            $resultPage->getConfig()->getTitle()->prepend(__('Orders'));
            return $resultPage;
        }
    }
}
