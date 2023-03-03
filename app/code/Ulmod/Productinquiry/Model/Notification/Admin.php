<?php
/*** Copyright © Ulmod. All rights reserved. **/
namespace Ulmod\Productinquiry\Model\Notification;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Ulmod\Productinquiry\Model\ConfigData;
use Magento\Framework\Event\Observer as EventObserver;
use Psr\Log\LoggerInterface;
use Magento\Framework\Escaper;
        
class Admin implements ObserverInterface
{
    /**
     * @var StateInterface
     */
    protected $inlineTranslation;
    
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    
    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;
    
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    
    /**
     * @var ConfigData
     */
    public $configData;
    
    /**
     * @var LoggerInterface
     */
    protected $logger;
    
    /**
     * @var Escaper
     */
    protected $_escaper;
    
    /**
     * @param StateInterface $inlineTranslation
     * @param StoreManagerInterface $storeManager
     * @param TransportBuilder $transportBuilder
     * @param ScopeConfigInterface $scopeConfig
     * @param ConfigData $configData
     * @param LoggerInterface $logger
     * @param Escaper $escaper
     */
    public function __construct(
        StateInterface $inlineTranslation,
        StoreManagerInterface $storeManager,
        TransportBuilder $transportBuilder,
        ScopeConfigInterface $scopeConfig,
        ConfigData $configData,
        LoggerInterface $logger,
        Escaper $escaper
    ) {
        $this->inlineTranslation = $inlineTranslation;
        $this->storeManager = $storeManager;
        $this->transportBuilder = $transportBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->configData = $configData;
        $this->logger = $logger;
        $this->_escaper = $escaper;
    }
    
    /**
     * Send mail to admin
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        $item = $observer->getEvent()->getItem();
        $isNotifEnabled = $this->configData->isAdminNotificationEnabled();
        if ($item->getId() == null && $isNotifEnabled) {
            $store = $this->storeManager->getStore($item->getStoreId());
            $image = $item->getImage() ? __("Yes") : __("No");
            $statuses = $item->getAvailableStatuses();
            $status = $statuses[$item->getStatus()];
            $product_name = $item->getProductName();
            $product_sku = $item->getProductSku();
            $vars = [
                'user_name' => $item->getName(),
                'user_email' => $item->getEmail(),
                'message' => $item->getMessage(),
                'telephone' => $item->getTelephone(),
                'subject' => $item->getSubject(),
                'current_page_url' => $item->getCurrentPageUrl(),
                'image' =>  $image,
                'status' => $status,
                'product_name' => $product_name,
                'product_sku' => $product_sku,
                'extra_field_one' => $item->getExtraFieldOne(),
                'extra_field_two' => $item->getExtraFieldTwo(),
                'extra_field_three' => $item->getExtraFieldThree(),
                'extra_field_four' => $item->getExtraFieldFour(),
                'store_view' => $store->getFrontendName()
            ];

            $this->inlineTranslation->suspend();
            try {
                $adminEmailSender = [
                    'name' => $this->_escaper->escapeHtml($this->configData->getEmailSenderName()),
                    'email' => $this->_escaper->escapeHtml($this->configData->getEmailSender()),
                ];

                $templateId = $this->configData->getAdminEmailTemplate();

                $this->transportBuilder
                    ->setTemplateIdentifier($templateId)
                    ->setTemplateOptions(
                        [
                            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                            'store' => $this->storeManager->getStore($item->getStoreId())->getId()
                        ]
                    )
                    ->setTemplateVars($vars);
                    $this->transportBuilder->setFrom($adminEmailSender);
                    $this->transportBuilder->addTo($this->configData->getAdminEmail());

                    $copyTo = $this->getEmailCopyTo();
                if (!empty($copyTo)) {
                    foreach ($copyTo as $bccEmail) {
                        $this->transportBuilder->addBcc($bccEmail);
                    }
                }
                    $this->transportBuilder->getTransport()->sendMessage();
                
                $this->inlineTranslation->resume();
            } catch (\Magento\Framework\Exception\MailException $e) {
                $this->logger->error($e->getMessage());
            }
        }
        return $this;
    }

    /**
     * Return email copy_to list
     *
     * @return array|bool
     */
    public function getEmailCopyTo()
    {
        $data = $this->configData->getAdminEmailBcc();
        if (!empty($data)) {
            return array_map('trim', explode(',', $data));
        }
        return false;
    }
}
