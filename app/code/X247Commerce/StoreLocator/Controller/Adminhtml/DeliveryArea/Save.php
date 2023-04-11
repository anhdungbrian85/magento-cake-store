<?php

namespace X247Commerce\StoreLocator\Controller\Adminhtml\DeliveryArea;

use X247Commerce\StoreLocator\Model\DeliveryAreaFactory;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use RuntimeException;

class Save extends Action
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

        $resultRedirect = $this->resultRedirectFactory->create();
        $dataSave = [
            'postcode' => $data['postcode'],
            'name' => $data['name'],
            'status' => $data['status'],
            'matching_strategy' => $data['matching_strategy'],
            'store_id' => isset($data['store_id']) ? $data['store_id'] : null,
        ];
        $deliveryArea = $this->deliveryArea->create();
        if (!empty($data['id'])) {
            $deliveryArea->load($data['id']);

            if (!$deliveryArea->getId()) {
                $this->messageManager->addError(__("Delivery area is not exist."));
                return $resultRedirect->setPath('*/*/edit');
            }
            $dataSave['id'] = $data['id'];
            $deliveryArea->addData($dataSave);
            // var_dump('update');
            // var_dump($deliveryArea->getData());die();
            $deliveryArea->save();
        } else {
            $deliveryArea->setData($dataSave);
            // var_dump('new');
            // var_dump($deliveryArea->getData());die();
            $deliveryArea->save();
        }        
        $this->messageManager->addSuccess('Save Success');
        return $resultRedirect->setPath('*/*/index');
    }
}
