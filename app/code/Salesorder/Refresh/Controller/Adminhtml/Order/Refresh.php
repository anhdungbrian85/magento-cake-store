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
      
            //$admin_base_url = $objectManager->create('Magento\Backend\Helper\Data')->getUrl('sales/order');
            $sec = "60";

            echo "<html>";
            echo" <head>";
            echo " <meta http-equiv='refresh' content=".$sec.">";
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
