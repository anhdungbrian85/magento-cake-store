<?php

namespace X247Commerce\StoreLocator\Controller\Adminhtml\DeliveryArea;

use X247Commerce\StoreLocator\Model\DeliveryArea;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

class Edit extends Action
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $registry = null;

    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var DeliveryAreaFactory
     */
    protected $_model;

    /**
     * @param Action\Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     * @param LocationFactory $model
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory,
        Registry $registry,
        DeliveryArea $model
    )
    {
        $this->_resultPageFactory = $resultPageFactory;
        $this->registry = $registry;
        $this->_model = $model;
        parent::__construct($context);
    }

    /**
     * @return Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->_model;
        $area = null;
        if ($id) {
            $area = $model->load($id);
            if (!$area->getId()) {
                $this->messageManager->addError(__('This delivery area not exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
        }
        $data = $this->_getSession()->getFormData(true);
        if (!empty($data)) {
            $area->setData($data);
        }
        $this->registry->register('delivery_area', $area);
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Delivery Area') : __('New Delivery Area'),
            $id ? __('Edit Delivery Area') : __('New Delivery Area')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Delivery Area Management'));
        
        $resultPage->getConfig()->getTitle()
            ->prepend($area ? $area->getName() : __('Delivery Area'));
        return $resultPage;
    }

    /**
     * Init actions
     *
     * @return Page
     */
    protected function _initAction()
    {
        /** @var Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('X247Commerce_StoreLocator::deliveryarea')
                    ->addBreadcrumb(__('Delivery Area'), __('Delivery Area'))
                    ->addBreadcrumb(__('Manage Delivery Area'), __('Manage Delivery Area'));
        return $resultPage;
    }
}
