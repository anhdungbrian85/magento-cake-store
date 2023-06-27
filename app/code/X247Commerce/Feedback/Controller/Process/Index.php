<?php
namespace X247Commerce\Feedback\Controller\Process;

use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Mail\Template\TransportBuilder;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $inlineTranslation;
    protected $escaper;
    protected $transportBuilder;    
    protected $dataHepler;
    protected $storeManager;
    protected $logger;
    protected $fileUploaderFactory;
    protected $fileSystem;
    protected $franchiseHelper;
    const FEEDBACK_ABSOLUTE_PATH = 'feedback';
    const FEEDBACK_EMAIL_TEMPLATE = 'feeback_email_template';
    private $countRecursion;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        StateInterface $inlineTranslation,
        Escaper $escaper,
        TransportBuilder $transportBuilder,
        \X247Commerce\Franchise\Helper\Data $dataHepler,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \X247Commerce\Franchise\Helper\Data $franchiseHelper,
        \Magento\Framework\Filesystem $fileSystem
    ) {
        parent::__construct($context);
        $this->inlineTranslation = $inlineTranslation;
        $this->escaper = $escaper;
        $this->transportBuilder = $transportBuilder;
        $this->dataHepler = $dataHepler;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->fileSystem = $fileSystem;
        $this->franchiseHelper = $franchiseHelper;
    }

    public function execute()
    {
        $data = $this->getDataPost();
        $sender = $this->franchiseHelper->getEmailSender();
        $sendto = $this->franchiseHelper->getEmailFranchise();
        $storeId = $this->storeManager->getStore()->getId();

        $this->sendMail($sender, $sendto, self::FEEDBACK_EMAIL_TEMPLATE, ['data' => $data], $storeId);
        return $this->resultRedirectFactory->create()->setPath('feedback/success');
    }
    

    protected function getDataPost(){
        $request = $this->getRequest();
        $dataRequest = $request->getPostValue();
        $data = $this->getFormField($dataRequest);
        return $this->escaperDataList($data);
    }

    protected function getFormField($data){
        return [
        "name" => $data['name'] ?? '',
        "phone" => $data['phone'] ?? '',
        "email" => $data['email'] ?? '',
        "day" => $data['day'] ?? '',
        "month" => $data['month'] ?? '',
        "year" => $data['year'] ?? '',
        "store" => $data['store'] ?? '',
        "comments" => $data['comments'] ?? '',
        "rating" => $data['rating'] ?? '',
        "image" => $this->uploadImage(),
        ];
    }

    protected function uploadImage(){
        try{
        $uploader = $this->fileUploaderFactory->create(['fileId' => 'image']);
        $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
        $uploader->setAllowRenameFiles(true);
        $uploader->setFilesDispersion(false);
        $path = $this->fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)
        ->getAbsolutePath(self::FEEDBACK_ABSOLUTE_PATH);
            $uploader->save($path);
            $imageName = $uploader->getUploadedFileName();
            return $this->createUrlFeedBackMedia($imageName);
        }catch(\Exception $e){
            $this->logger->debug($e->getMessage());
            return '';
        }

    }

    protected function createUrlFeedBackMedia($imageName){
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).self::FEEDBACK_ABSOLUTE_PATH.'/'.$imageName;
    }

    protected function escaperDataList($data){
        foreach($data as $key => $field){
            if(is_array($field)){
                $data[$key] = $this->escaperDataList($field);
            }else{
                $data[$key] = $this->escaper->escapeHtml($field);
            }

            $this->countRecursion ++;
            if($this->countRecursion >= 200){// handler recursion if guest fix form request
                $this->messageManager->addErrorMessage(__('Something Went Wrong.'));
                return [];
            }
        }
        return $data;
    }

    protected function sendMail($sender, $sendto, $emailTemplate, $templateVars, $storeId)
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
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }

        return false;
    }
    

}