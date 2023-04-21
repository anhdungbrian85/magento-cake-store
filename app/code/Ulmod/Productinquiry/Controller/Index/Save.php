<?php
/*** Copyright © Ulmod. All rights reserved. **/
 
namespace Ulmod\Productinquiry\Controller\Index;

use Ulmod\Productinquiry\Api\Data\DataInterface;
use Magento\Store\Model\ScopeInterface;
use Ulmod\Productinquiry\Model\Data as ProductinquiryModel;
use Magento\Framework\App\Action\Context;
use Magento\Store\Model\StoreManagerInterface;
use Ulmod\Productinquiry\Model\ConfigData;
use Magento\Framework\Session\Generic as InquiySession;
use Ulmod\Productinquiry\Model\Data\Image as ImageModel;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Ulmod\Productinquiry\Model\Upload as UploadModel;
use Ulmod\Productinquiry\Model\DataFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Controller\ResultFactory;

class Save extends \Magento\Framework\App\Action\Action
{
    /**
     * @var InquiySession
     */
    protected $inquirySession;
    
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    
    /**
     * @var ConfigData
     */
    protected $configData;
    
    /**
     * @var UploadModel
     */
    protected $uploadModel;
    
    /**
     * @var ImageModel
     */
    protected $imageModel;

    /**
     * @var DataFactory
     */
    protected $dataFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;
    
    /**
     * @var File
     */
    private $driverFile;
 
    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirector;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param ConfigData $configData
     * @param InquiySession $inquirySession
     * @param ImageModel $imageModel
     * @param RemoteAddress $remoteAddress
     * @param DataFactory $dataFactory
     * @param UploadModel $uploadModel
     * @param File $driverFile
     * @param LoggerInterface $logger
     * @param RedirectInterface $redirector
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        ConfigData $configData,
        InquiySession $inquirySession,
        ImageModel $imageModel,
        RemoteAddress $remoteAddress,
        DataFactory $dataFactory,
        UploadModel $uploadModel,
        File $driverFile,
        LoggerInterface $logger,
        RedirectInterface $redirector
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->configData = $configData;
        $this->inquirySession = $inquirySession;
        $this->_remoteAddress = $remoteAddress;
        $this->uploadModel = $uploadModel;
        $this->dataFactory = $dataFactory;
        $this->imageModel = $imageModel;
        $this->driverFile = $driverFile;
        $this->logger = $logger;
        $this->redirector = $redirector;
    }
    
    /**
     * Save user inquiry
     *
     * @return void
     * @throws \Exception
     */
    public function execute()
    {
        $post = $this->getRequest()->getPostValue();
        if (!$post) {
            $this->_redirectReferer();
            
            return;
        }
        try {
            $error = false;
            if (!\Zend_Validate::is(trim($post['name']), 'NotEmpty')) {
                $error = true;
            }
            if (!\Zend_Validate::is(trim($post['message']), 'NotEmpty')) {
                $error = true;
            }
            if (!\Zend_Validate::is(trim($post['email']), 'EmailAddress')) {
                $error = true;
            }
            if ($error) {
                throw new LocalizedException(__('Please fill all required fields.'));
            }
            $post['store_id'] = $this->storeManager->getStore()
                ->getId();
            $post['status'] = ProductinquiryModel::STATUS_NEW;
            
            $model = $this->dataFactory->create();
            $model->setData($post);

            if ($this->configData->isAttachmentEnabled() == 1) {
                $imageName = $this->uploadModel
                    ->uploadFileAndGetName(
                        'image',
                        $this->imageModel->getBaseDir(),
                        $post,
                        ['jpg','jpeg','gif','png', 'bmp']
                    );
                $model->setImage($imageName);
            }
            
            // send emails
            $this->_eventManager->dispatch(
                'productinquiry_save_new',
                ['item' => $model]
            );
            $this->_eventManager->dispatch(
                'productinquiry_autoreply',
                ['item' => $model]
            );
            
            $model->save();
            
            $this->messageManager->addSuccess(
                __($this->configData->getSentMessage())
            );

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->inquirySession->setFormData(
                $post
            )->setRedirectUrl(
                $this->redirector->getRefererUrl()
            );
        }
        
        $redirect = $this->resultFactory->create(
            ResultFactory::TYPE_REDIRECT
        );
        $redirect->setUrl($this->redirector->getRefererUrl());
        return $redirect;
    }
}
