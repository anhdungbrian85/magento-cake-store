<?php
/*** Copyright © Ulmod. All rights reserved. **/
 
namespace Ulmod\Productinquiry\Controller\Adminhtml\Index;

class Delete extends \Magento\Backend\App\Action
{
    /**
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Ulmod_Productinquiry::delete';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Ulmod\Productinquiry\Model\DataFactory
     */
    protected $dataFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Ulmod\Productinquiry\Model\DataFactory $dataFactory
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Ulmod\Productinquiry\Model\DataFactory $dataFactory,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->dataFactory = $dataFactory;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }
    
    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('inquiry_id');
        if ($id) {
            try {
                $model = $this->dataFactory->create();
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccess(__('Inquiry was deleted.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['inquiry_id' => $id]);
            }
        }
        $this->messageManager->addError(__('Can\'t find a inquiry to delete.'));
        return $resultRedirect->setPath('*/*/');
    }
}
