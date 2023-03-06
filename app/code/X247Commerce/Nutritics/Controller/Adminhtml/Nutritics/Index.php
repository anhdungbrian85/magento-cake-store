<?php

namespace X247Commerce\Nutritics\Controller\Adminhtml\Nutritics;
 
use Magento\Framework\Controller\ResultFactory;
 
class Index extends \Magento\Backend\App\Action
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->prepend(__('Nutritics Infomation Listing'));
        return $resultPage;
    }
}