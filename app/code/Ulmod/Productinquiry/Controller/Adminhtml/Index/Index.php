<?php
/*** Copyright © Ulmod. All rights reserved. **/
 
namespace Ulmod\Productinquiry\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
    public const ADMIN_RESOURCE = 'Ulmod_Productinquiry::productinquiry';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ulmod_Productinquiry::productinquiry');
        $resultPage->addBreadcrumb(__('Product Inquiries'), __('Product Inquiries'));
        $resultPage->addBreadcrumb(__('Manage Inquiries'), __('Manage Inquiries'));
        $resultPage->getConfig()->getTitle()->prepend(__('Product Inquiries'));

        return $resultPage;
    }
}
