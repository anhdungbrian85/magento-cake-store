<?php

namespace X247Commerce\Yext\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Backend\Helper\Data;

class YextHelper extends AbstractHelper
{
    const DELETE_ADMIN_SYNC_SETTING_PATH = 'yext/sync_settings/delete_admin';
    const DELETE_SOURCE_SYNC_SETTING_PATH = 'yext/sync_settings/delete_source';
    const NOTIFICATION_EMAIL_TEMPLATE_CREATED_USER = 'yext/email_template/create_user';

    protected $transportBuilder;
    protected $storeManager;
    protected $inlineTranslation;
    protected $logger;
    protected $backendHelper;

    public function __construct(
        Context $context,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        StateInterface $state,
        Data $backendHelper
    ) 
    {
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->inlineTranslation = $state;
        $this->logger = $context->getLogger();
        $this->backendHelper = $backendHelper;
        parent::__construct($context);
    }

    public function getUrlKeyFromName($name)
    {        
        $url_key = strtolower($name);
        $url_key = str_replace('cake box', '', $url_key);
        $url_key = trim($url_key);
        $url_key = preg_replace("/\s+/", "-", $url_key);
        return $url_key;
    }

    /**
     * Get sync setting webhook when delete store location on Yext
     *
     * @return boolean
     */
    public function getDeleteAdminSyncSetting()
    {
        return  $this->scopeConfig->getValue(self::DELETE_ADMIN_SYNC_SETTING_PATH, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get sync setting webhook when delete store location on Yext
     *
     * @return boolean
     */
    public function getDeleteSourceSyncSetting()
    {
        return  $this->scopeConfig->getValue(self::DELETE_SOURCE_SYNC_SETTING_PATH, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Convert from Yext Open Hours to Amasty Schedule
     * 
     * @param $yextSchedule array
     * 
     * @return array
     */
    public function convertSchedule($yextSchedule)
    {
        // $yextSchedule = json_decode($yextHours);

        $amastySchedule = [];

        $amastySchedule =   [   
                                "monday" => $this->convertWeekDay($yextSchedule, "monday"),
                                "tuesday" => $this->convertWeekDay($yextSchedule, "tuesday"),
                                "wednesday" => $this->convertWeekDay($yextSchedule, "wednesday"),
                                "thursday" => $this->convertWeekDay($yextSchedule, "thursday"),
                                "friday" => $this->convertWeekDay($yextSchedule, "friday"),
                                "saturday" => $this->convertWeekDay($yextSchedule, "saturday"),
                                "sunday" => $this->convertWeekDay($yextSchedule, "sunday")
                            ];

        return $amastySchedule;
    }

    /**
     * Convert from week day of Yext Open Hours to week day of Amasty Schedule
     * 
     * @param $yextSchedule array, $day week day
     * 
     * @return array
     */
    public function convertWeekDay($yextSchedule, $day) 
    {
        $amastySchedule = [];
        if (!$yextSchedule[$day]["isClosed"]) {
            $amastySchedule[$day."_status"] = 1;
            $openTime = $yextSchedule[$day]["openIntervals"];
            if ($openTime) {
                $amastySchedule["from"]["hours"] = explode(':', $openTime[0]["start"])[0];
                $amastySchedule["from"]["minutes"] = explode(':', $openTime[0]["start"])[1];
                $amastySchedule["break_from"]["hours"] = isset($openTime[1]) ? explode(':', $openTime[0]["end"])[0] : "00";
                $amastySchedule["break_from"]["minutes"] = isset($openTime[1]) ? explode(':', $openTime[0]["end"])[1] : "00";
                $amastySchedule["break_to"]["hours"] = isset($openTime[1]) ? explode(':', $openTime[1]["start"])[0] : "00";
                $amastySchedule["break_to"]["minutes"] = isset($openTime[1]) ? explode(':', $openTime[1]["start"])[1] : "00";
                $amastySchedule["to"]["hours"] = isset($openTime[1]) ? explode(':', $openTime[1]["end"])[0] : explode(':', $openTime[0]["end"])[0];
                $amastySchedule["to"]["minutes"] = isset($openTime[1]) ? explode(':', $openTime[1]["end"])[1] : explode(':', $openTime[0]["end"])[1];
            }            
        } else {
            $amastySchedule[$day."_status"] = 0;
            $amastySchedule["from"]["hours"] = "00";
            $amastySchedule["from"]["minutes"] = "00";
            $amastySchedule["break_from"]["hours"] = "00";
            $amastySchedule["break_from"]["minutes"] = "00";
            $amastySchedule["break_to"]["hours"] = "00";
            $amastySchedule["break_to"]["minutes"] = "00";
            $amastySchedule["to"]["hours"] = "00";
            $amastySchedule["to"]["minutes"] = "00";
        }

        return $amastySchedule;
    }

    /**
     * get random string
     * 
     * @param $textLength string length
     * 
     * @return string
     */
    public function randomString($textLength)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
     
        for ($i = 0; $i < $textLength; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }
     
        return $randomString;
    }

    /**
     * send notification email to staff admin
     * 
     * @param $username, $password, $sendToEmail
     * 
     * @return void
     */
    public function sendEmail($username, $password, $sendToEmail)
    {
        $templateConfigPath = self::NOTIFICATION_EMAIL_TEMPLATE_CREATED_USER;

        try {
            // template variables pass here
            $templateVars = [
                'backend_url' => $this->backendHelper->getHomePageUrl(),
                'username' => $username,
                'password' => $password
            ];

            $storeId = $this->storeManager->getStore()->getId();
            $templateId = $this->scopeConfig->getValue(self::NOTIFICATION_EMAIL_TEMPLATE_CREATED_USER, ScopeInterface::SCOPE_STORE, $storeId);
            $this->inlineTranslation->suspend();

            $storeScope = ScopeInterface::SCOPE_STORE;
            $templateOptions = [
                'area' => \Magento\Framework\App\Area::AREA_ADMINHTML,
                'store' => $storeId
            ];
            $transport = $this->transportBuilder->setTemplateIdentifier($templateId, $storeScope)
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->setFromByScope('general')
                ->addTo($sendToEmail)
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }

}
