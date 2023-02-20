<?php

namespace X247Commerce\StoreLocator\Controller\Adminhtml\DeliveryArea;

use X247Commerce\StoreLocator\Model\DeliveryAreaFactory;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use RuntimeException;

class Delete extends Action
{
    protected $deliveryArea;

    public function __construct(
        Action\Context $context,
        DeliveryAreaFactory $deliveryArea
    ) {
        parent::__construct($context);
        $this->deliveryArea = $deliveryArea;
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $id = $this->getRequest()->getParam('id');

        $resultRedirect = $this->resultRedirectFactory->create();
        
        $deliveryArea = $this->deliveryArea->create();
        if (!empty($id)) {
            $deliveryArea->load($id);

            if (!$deliveryArea->getId()) {
                $this->messageManager->addErrorMessage(__("Delivery area is not exist."));
                return $resultRedirect->setPath('*/*/index');
            }
            
            $deliveryArea->delete();
            $this->messageManager->addSuccessMessage('Delete Success');
        } else {
            $this->messageManager->addErrorMessage(__('We can\'t find a Delivery Area to delete.'));
            return $resultRedirect->setPath('*/*/index');
        }
        
        return $resultRedirect->setPath('*/*/index');
    }
}
