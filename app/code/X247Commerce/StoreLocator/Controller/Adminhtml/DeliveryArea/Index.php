<?php

namespace X247Commerce\StoreLocator\Controller\Adminhtml\DeliveryArea;
 
use Magento\Framework\Controller\ResultFactory;
 
class Index extends \Magento\Backend\App\Action
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->prepend(__('Delivery Area Listing'));
        return $resultPage;
    }
}