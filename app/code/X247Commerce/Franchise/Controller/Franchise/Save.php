<?php

namespace X247Commerce\Franchise\Controller\Franchise;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Mail\Template\TransportBuilder;


class Save extends Action
{
    protected $inlineTranslation;
    protected $escaper;
    protected $transportBuilder;    
    protected $dataHepler;
    protected $storeManager;
    protected $logger;
    protected $eventManager;
    private $countRecursion = 0;

    const TEMPLATE_EMAIL_ADMIN = 'franchise_admin_email_template';
    const TEMPLATE_EMAIL_CLIENT = 'franchise_client_email_template';

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        StateInterface $inlineTranslation,
        Escaper $escaper,
        TransportBuilder $transportBuilder,
        \X247Commerce\Franchise\Helper\Data $dataHepler,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->inlineTranslation = $inlineTranslation;
        $this->escaper = $escaper;
        $this->transportBuilder = $transportBuilder;
        $this->dataHepler = $dataHepler;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    public function execute()
    {
        $data = $this->getDataPost();
        if(is_array($data['address'])){
            $data['address'] = implode(' ', $data['address']);
        }
        $this->sendMailAdmin($data);
        if(!empty($data['marketing_permission'])){
            $this->sendMailClient($data);
        }
        return $this->resultRedirectFactory->create()->setPath($this->_redirect->getRefererUrl());
    }

    protected function sendMailAdmin($data){
        $email = $this->getEmailAdmin();
        $storeId = $this->getStoreId();
        $sender = $this->getEmailSender();
        $data['data'] = $data;
        $this->sendMail($sender, $email, self::TEMPLATE_EMAIL_ADMIN, $data, $storeId);
    }

    protected function sendMailClient($data){
        $email = $data['email'];
        $storeId = $this->getStoreId();
        $sender = $this->getEmailSender();
        $data['data'] = $data;
        $this->sendMail($sender, $email, self::TEMPLATE_EMAIL_CLIENT, $data, $storeId);
    }

    public function sendMail($sender, $sendto, $emailTemplate, $templateVars, $storeId)
    {
        if(empty($templateVars))
        {
            return ;
        }

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
            $this->messageManager->addSuccessMessage(__('You send the franchise request.'));
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }

        return false;
    }

    protected function escaperDataList($data){
        foreach($data as $key => $field){
            if(is_array($field)){
                $data[$key] = $this->escaperDataList($field);
            }else{
                $data[$key] = $this->escaper->escapeHtml($field);
            }
            $this->countRecursion ++;
            
            if($this->countRecursion >= 200){ // handler recursion if guest fix form request
                $this->messageManager->addErrorMessage(__('Something Went Wrong.'));
                return $this->resultRedirectFactory->create()->setPath($this->_redirect->getRefererUrl());
            }
        }
        return $data;
    }

    protected function getDataPost(){
        $dataRequest = $this->getRequest()->getPostValue();
        $data = $this->getFormField($dataRequest);
        return $this->escaperDataList($data);
    }

    protected function getFormField($data){
        return [
        "first_name" => $data['FNAME'] ?? '',
        "last_name" => $data['LNAME'] ?? '',
        "email" => $data['EMAIL'] ?? '',
        "address" => $data['ADDRESS'] ?? '',
        "phone" => $data['PHONE'] ?? '',
        "employment" => $data['EMPLOYMENT'] ?? '',
        "location" => $data['LOCATION'] ?? '',
        "why_area" => $data['WHYAREA'] ?? '',
        "experience_business" => $data['MMERGE8'] ?? '',
        "description_experience" => $data['EXPERIENCE'] ?? '',
        "nearest_branch" => $data["NEARESTBRA"] ?? '',
        "manager" => $data['MANAGER'] ?? '',
        "franchise_opportunity_source" => $data['MMERGE12'] ?? '',
        "marketing_permission" => $data['gdpr'] ?? ''
        ];
    }

    protected function getEmailAdmin(){
        return $this->dataHepler->getEmailFranchise();
    }
    
    protected function getStoreId(){
        return $this->storeManager->getStore()->getId();
    }

    protected function getEmailSender(){
        return $this->dataHepler->getEmailSender();
    }
}