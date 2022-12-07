<?php
/*** Copyright © Ulmod. All rights reserved. **/
 
namespace Ulmod\Productinquiry\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\Model\Session as BackendSession;

class Edit extends Action
{
    /**
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Ulmod_Productinquiry::productinquiry';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Ulmod\Productinquiry\Model\DataFactory
     */
    protected $dataFactory;

    /**
     * @var BackendSession
     */
    protected $backendSession;
    
    /**
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Ulmod\Productinquiry\Model\DataFactory $dataFactory
     * @param \Magento\Framework\Registry $registry
     * @param BackendSession $backendSession
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Ulmod\Productinquiry\Model\DataFactory $dataFactory,
        \Magento\Framework\Registry $registry,
        BackendSession $backendSession
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->dataFactory = $dataFactory;
        $this->_coreRegistry = $registry;
        $this->backendSession = $backendSession;
        parent::__construct($context);
    }

    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ulmod_Productinquiry::productinquiry')
            ->addBreadcrumb(__('Productinquiry'), __('Productinquiry'))
            ->addBreadcrumb(__('Manage Productinquiry'), __('Manage Productinquiry'));
        return $resultPage;
    }

    /**
     * Edit Blog post
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('inquiry_id');

        /** @var \Ulmod\Productinquiry\Model\Data $model */
        $model = $this->dataFactory->create();

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This inquiry no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
        }

        $data = $this->backendSession->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        $this->_coreRegistry->register('inquiry', $model);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Inquiry') : __('New Inquiry'),
            $id ? __('Edit Inquiry') : __('New Inquiry')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Productinquiry'));
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? $model->getName() : __('New Inquiry'));

        return $resultPage;
    }
}
