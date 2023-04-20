<?php

namespace X247Commerce\CustomerInquiry\Controller\Inquiry;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\DataPersistorInterface;
use X247Commerce\CustomerInquiry\Model\InquiryFactory;

class Save extends Action
{
    private $inquiryFactory;
    
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        InquiryFactory $inquiryFactory
    ) {
        parent::__construct($context);
        $this->inquiryFactory = $inquiryFactory;
    }
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        
        $newData = [
            'customer_name' => $data['name'],
            'customer_email' => $data['email'],
            'message' => $data['message'],
            'enquiry_type' => $data['enquiry_type']
        ];
        $post = $this->inquiryFactory->create();
        
        try {
            $post->addData($newData);
            $post->save();
            $this->messageManager->addSuccessMessage(__('You send the enquiry.'));
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }
        return $this->resultRedirectFactory->create()->setPath($this->_redirect->getRefererUrl());
    }
}