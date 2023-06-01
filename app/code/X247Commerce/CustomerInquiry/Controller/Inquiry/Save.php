<?php

namespace X247Commerce\CustomerInquiry\Controller\Inquiry;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\DataPersistorInterface;
use X247Commerce\CustomerInquiry\Model\InquiryFactory;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Mail\Template\TransportBuilder;
use Amasty\Storelocator\Model\LocationFactory;
use Magento\Store\Model\StoreManagerInterface;


class Save extends Action
{
    private $inquiryFactory;
    protected $inlineTranslation;
    protected $escaper;
    protected $transportBuilder;    
    protected $logger;
    protected $locationModel;
    protected $storeManager;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        InquiryFactory $inquiryFactory,
        StateInterface $inlineTranslation,
        Escaper $escaper,
        TransportBuilder $transportBuilder,
        LocationFactory $locationModel,
        StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->inquiryFactory = $inquiryFactory;
        $this->inlineTranslation = $inlineTranslation;
        $this->escaper = $escaper;
        $this->transportBuilder = $transportBuilder;
        $this->locationModel = $locationModel;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        $post = $this->inquiryFactory->create();
        $storeId =  $this->storeManager->getStore()->getId();
        $locationEmail = $this->getLocation($this->getLocationId($data))->getEmail();
        $sender = [
            'name' => $this->escaper->escapeHtml($data['name']),
            'email' => $this->escaper->escapeHtml($data['email']),
        ];
        $templateVars = ['data' => [
            'message' => $this->escaper->escapeHtml($data['message']),
            'email' => $this->escaper->escapeHtml($data['email']),
            'name' => $this->escaper->escapeHtml($data['name']),
            'confirm_email' => $this->escaper->escapeHtml($data['confirm_email'])
        ]];
        $newData = [
            'customer_name' =>  $this->escaper->escapeHtml($data['name']),
            'customer_email' =>  $this->escaper->escapeHtml($data['email']),
            'message' =>  $this->escaper->escapeHtml($data['message']),
            'enquiry_type' =>  $this->escaper->escapeHtml($data['enquiry_type'])
        ];
        try {
            $this->sendMail($sender, $locationEmail, 'inquiry_email_template', $templateVars, $storeId);
            $post->addData($newData);
            $post->save();
            $this->messageManager->addSuccessMessage(__('You send the enquiry.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }

        return $this->resultRedirectFactory->create()->setPath($this->_redirect->getRefererUrl());
    }

    public function sendMail($sender, $sendto, $emailTemplate, $templateVars,$storeId)
    {
        try {
            $this->inlineTranslation->suspend();

            $transport = $this->transportBuilder
                ->setTemplateIdentifier($emailTemplate)
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => $storeId,
                    ]
                )
                ->setTemplateVars($templateVars)
                ->setFrom($sender)
                ->addTo($sendto)
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }

        return false;
    }

    protected function getLocation($id){
        try{
            return $this->locationModel->create()->load($id);
        } catch(\Exception $e){
        }
        return $this->locationModel;
    }

    protected function getLocationId($data){
        return empty($data['location_id']) ? null : $data['location_id'];
    }
}