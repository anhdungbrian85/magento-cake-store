<?php
/*** Copyright © Ulmod. All rights reserved. **/
 
namespace Ulmod\Productinquiry\Model;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Request\Http as RequestHttp;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\Locale\Resolver;

class ConfigData
{
    public const GOOGLE_VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';
    public const GOOGLE_SCRIPT_URL = 'https://www.google.com/recaptcha/api.js';

    /**
     * Path to store config for general section
     *
     * @var string|int
     */
    public const XML_PATH_GENERAL_EXTENSION_STATUS   = 'productinquiry/general/enabled';
    public const XML_PATH_GENERAL_INQUIRY_ALL_STATUS   = 'productinquiry/general/inquiry_for_all_products';
    public const XML_PATH_GENERAL_ENABLE_FOR_CATEGORY   = 'productinquiry/general/inquiry_for_category';
    public const XML_PATH_GENERAL_INQUIRY_LINK_TEXT  = 'productinquiry/general/inquiry_link_text';

    /**
     * Path to store config for forms section
     *
     * @var string|int
     */
    public const XML_PATH_FORM_FORMTYPE = 'productinquiry/form/type/formtype';
    public const XML_PATH_FORM_LAYOUT    = 'productinquiry/form/type/layout';
    public const XML_PATH_FORM_TITLE   = 'productinquiry/form/type/formtitle';

    public const XML_PATH_FORM_PRODUCT_IMAGE  = 'productinquiry/form/type/show_product_image';
    public const XML_PATH_FORM_PRODUCT_NAME  = 'productinquiry/form/type/show_product_name';
    public const XML_PATH_FORM_PRODUCT_SKU = 'productinquiry/form/type/show_product_sku';

    public const XML_PATH_FORM_FIELDS_ENABLE_ATTACHMENT = 'productinquiry/form/fields/enable_attachment';
    public const XML_PATH_FORM_FIELDS_ENABLE_SUBJECT = 'productinquiry/form/fields/enable_subject';
    public const XML_PATH_FORM_FIELDS_ENABLE_PHONE  = 'productinquiry/form/fields/enable_phone';
    public const XML_PATH_FORM_ADDIF_FIELD_ONE_ENABLE = 'productinquiry/form/additiona_fields/field_one_enable';
    public const XML_PATH_FORM_ADDIF_FIELD_ONE_TYPE = 'productinquiry/form/additiona_fields/field_one_type';
    public const XML_PATH_FORM_ADDIF_FIELD_ONE_LABEL = 'productinquiry/form/additiona_fields/field_one_label';
    public const XML_PATH_FORM_ADDIF_FIELD_ONE_ORDER = 'productinquiry/form/additiona_fields/field_one_sortorder';
    public const XML_PATH_FORM_ADDIF_FIELD_TWO_ENABLE = 'productinquiry/form/additiona_fields/field_two_enable';
    public const XML_PATH_FORM_ADDIF_FIELD_TWO_TYPE = 'productinquiry/form/additiona_fields/field_two_type';
    public const XML_PATH_FORM_ADDIF_FIELD_TWO_LABEL = 'productinquiry/form/additiona_fields/field_two_label';
    public const XML_PATH_FORM_ADDIF_FIELD_TWO_ORDER = 'productinquiry/form/additiona_fields/field_two_sortorder';
    public const XML_PATH_FORM_ADDIF_FIELD_THREE_ENABLE = 'productinquiry/form/additiona_fields/field_three_enable';
    public const XML_PATH_FORM_ADDIF_FIELD_THREE_TYPE = 'productinquiry/form/additiona_fields/field_three_type';
    public const XML_PATH_FORM_ADDIF_FIELD_THREE_LABEL = 'productinquiry/form/additiona_fields/field_three_label';
    public const XML_PATH_FORM_ADDIF_FIELD_THREE_ORDER = 'productinquiry/form/additiona_fields/field_three_sortorder';
    public const XML_PATH_FORM_ADDIF_FIELD_FOUR_ENABLE = 'productinquiry/form/additiona_fields/field_four_enable';
    public const XML_PATH_FORM_ADDIF_FIELD_FOUR_TYPE = 'productinquiry/form/additiona_fields/field_four_type';
    public const XML_PATH_FORM_ADDIF_FIELD_FOUR_LABEL = 'productinquiry/form/additiona_fields/field_four_label';
    public const XML_PATH_FORM_ADDIF_FIELD_FOUR_ORDER = 'productinquiry/form/additiona_fields/field_four_sortorder';
    public const XML_PATH_FORM_PRIVACY_POLICY_FIELD_ENABLE
        = 'productinquiry/form/privacy_policy_field/privacy_policy_enable';
    public const XML_PATH_FORM_PRIVACY_POLICY_FIELD_MSG = 'productinquiry/form/privacy_policy_field/privacy_policy_msg';
    public const XML_PATH_FORM_RECAPTCHA_STATUS  = 'productinquiry/form/spam_block/recaptcha_status';

    public const XML_PATH_FORM_SPAM_BLOCK_ENABLE  = 'productinquiry/form/spam_block/enable';
    public const XML_PATH_FORM_RECAPTCHA_SECRET_KEY
        = 'productinquiry/form/spam_block/recaptcha_v2_checkbox_secret_key';
    public const XML_PATH_FORM_RECAPTCHA_SITE_KEY
        = 'productinquiry/form/spam_block/recaptcha_v2_checkbox_site_key';
    public const XML_PATH_FORM_HONEYPOT_NOT_ALLOWED_MSG
        = 'productinquiry/form/spam_block/honeypot_notallowed_message';
    public const XML_PATH_SENT_MESSAGE  = 'productinquiry/form/type/sent_message';

    /**
     * Path to store config for admin notifications
     */
    public const XML_PATH_ADMIN_EMAIL_ENABLED = 'productinquiry/inquiry_notification/notifyadmin';
    public const XML_PATH_ADMIN_EMAIL_SENDER_EMAIL = 'productinquiry/inquiry_notification/email_sender';
    public const XML_PATH_ADMIN_EMAIL   = 'productinquiry/inquiry_notification/admin_email';
    public const XML_PATH_ADMIN_EMAIL_BCC   = 'productinquiry/inquiry_notification/admin_email_bcc';
    public const XML_PATH_ADMIN_EMAIL_TEMPLATE = 'productinquiry/inquiry_notification/inquiry_template';
    
    /**
     * Path to store config for auto reply notifications
     */
    public const XML_PATH_AUTOREPLY_ENABLE   = 'productinquiry/inquiry_notification/enable_autoreply';
    public const XML_PATH_AUTOREPLY_TEMPLATE   = 'productinquiry/inquiry_notification/autoreply_template';
    public const XML_PATH_ADMIN_EMAIL_AUTOREPLY_SENDER_EMAIL
        = 'productinquiry/inquiry_notification/autoreply_email_sender';

    /**
     * @var RequestHttp
     */
    protected $request;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Registry
     */
    private $registry;
    
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;
    
    /**
     * @var SenderResolverInterface
     */
    private $senderResolver;
    
    /**
     * @var FilterProvider
     */
    private $filterProvider;
    
    /**
     * @var Resolver
     */
    private $localeResolver;
    
    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param UrlInterface $urlBuilder
     * @param RequestHttp $request
     * @param Registry $registry
     * @param StoreManagerInterface $storeManager
     * @param FilterProvider $filterProvider
     * @param SenderResolverInterface $senderResolver
     * @param Resolver $localeResolver
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        UrlInterface $urlBuilder,
        RequestHttp $request,
        Registry $registry,
        StoreManagerInterface $storeManager,
        FilterProvider $filterProvider,
        SenderResolverInterface $senderResolver,
        Resolver $localeResolver
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->urlBuilder = $urlBuilder;
        $this->request = $request;
        $this->registry = $registry;
        $this->storeManager = $storeManager;
        $this->filterProvider = $filterProvider;
        $this->senderResolver = $senderResolver;
        $this->localeResolver = $localeResolver;
    }

    /**
     * Get recaptcha scrip url
     *
     * @return string
     */
    public function getRecaptchaCheckboxV2ScriptsUrl()
    {
        $params = [
            'hl' => $this->localeResolver->getLocale(),
        ];

        $url = self::GOOGLE_SCRIPT_URL;
        $url .= '?'.http_build_query($params);

        return $url;
    }

    /**
     * Get current url
     *
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->urlBuilder->getCurrentUrl();
    }

    /**
     * Check if current page is product page
     *
     * @return bool
     */
    public function isProductPage()
    {
         $fullActionName = $this->request->getFullActionName();
        
        if ($fullActionName == 'catalog_product_view') {
            return true;
        }
        return false;
    }

    /**
     * Check if current page is inquiry form new page
     *
     * @return bool
     */
    public function isInquiryFormNewPage()
    {
         $fullActionName = $this->request->getFullActionName();
        
        if ($fullActionName == 'productinquiry_index_new') {
            return true;
        }
        return false;
    }

    /**
     * Get System Config values
     *
     * @param string $key
     * @return string|int|array|null
     */
    protected function _getConfig($key)
    {
        return $this->scopeConfig->getValue($key, ScopeInterface::SCOPE_STORE);
    }
    
    /**
     * Get extention status config value
     *
     * @return string
     */
    public function isExtensionEnabled()
    {
        return (int)$this->_getConfig(self::XML_PATH_GENERAL_EXTENSION_STATUS);
    }

    /**
     * Get inquiry for all status config value
     *
     * @return string
     */
    public function isInquiryForAllProducts()
    {
        return (int)$this->_getConfig(self::XML_PATH_GENERAL_INQUIRY_ALL_STATUS);
    }
    
    /**
     * Check if current page is category page
     *
     * @return bool
     */
    public function isCategoryPage()
    {
        $fullActionName = $this->request->getFullActionName();
        
        if ($fullActionName == 'catalog_category_view') {
            return true;
        }
        return false;
    }

    /**
     * Check if current page is catalog search result page
     *
     * @return bool
     */
    public function isCatalogSearchPage()
    {
        $fullActionName = $this->request->getFullActionName();
        
        if ($fullActionName == 'catalogsearch_result_index') {
            return true;
        }
        return false;
    }
    
    /**
     * Check if current page is category page or catalog search result page
     *
     * @return bool
     */
    public function isCategoryOrCatalogSearchPages()
    {
        if ($this->isCategoryPage() || $this->isCatalogSearchPage()) {
            return true;
        }
        return false;
    }
    
    /**
     * Check if inquiry is enabled on selected categories
     *
     * @return bool
     */
    public function isEnableInquiryOnCategory()
    {
        if ($this->isCategoryPage()) {
            $category = $this->registry->registry('current_category');
            $categoryId = $category->getId();
            return !in_array(
                $categoryId,
                explode(',', $this->_getConfig(self::XML_PATH_GENERAL_ENABLE_FOR_CATEGORY))
            );
        }
  
        return true;
    }
    
    /**
     * Get inquiry for link text config value
     *
     * @return string
     */
    public function getInquiryLinkText()
    {
        return (string)$this->_getConfig(self::XML_PATH_GENERAL_INQUIRY_LINK_TEXT);
    }
    
    /**
     * Get form type config value
     *
     * @return string
     */
    public function getFormType()
    {
        return (string)$this->_getConfig(self::XML_PATH_FORM_FORMTYPE);
    }

    /**
     * Get layout form config value
     *
     * @return string
     */
    public function getFormLayout()
    {
        return (string)$this->_getConfig(self::XML_PATH_FORM_LAYOUT);
    }
    
    /**
     * Get form title
     *
     * @return string
     */
    public function getFormTitle()
    {
        return (string)$this->_getConfig(self::XML_PATH_FORM_TITLE);
    }

    /**
     * Is show product image
     *
     * @return int
     */
    public function isShowProductImage()
    {
        return (int)$this->_getConfig(self::XML_PATH_FORM_PRODUCT_IMAGE);
    }

    /**
     * Is show product name
     *
     * @return int
     */
    public function isShowProductName()
    {
        return (int)$this->_getConfig(self::XML_PATH_FORM_PRODUCT_NAME);
    }

    /**
     * Is show product sku
     *
     * @return int
     */
    public function isShowProductSku()
    {
        return (int)$this->_getConfig(self::XML_PATH_FORM_PRODUCT_SKU);
    }

    /**
     * Check if telephone field is enabled
     *
     * @return int
     */
    public function isPhoneEnabled()
    {
        return (int)$this->_getConfig(self::XML_PATH_FORM_FIELDS_ENABLE_PHONE);
    }

    /**
     * Check if attachment is enabled
     *
     * @return int
     */
    public function isAttachmentEnabled()
    {
        return (int)$this->_getConfig(self::XML_PATH_FORM_FIELDS_ENABLE_ATTACHMENT);
    }
  
    /**
     * Check if subject is enabled
     *
     * @return int
     */
    public function isSubjectEnabled()
    {
        return (int)$this->_getConfig(self::XML_PATH_FORM_FIELDS_ENABLE_SUBJECT);
    }

    /**
     * Check if extra field one is enabled
     *
     * @return int
     */
    public function isExtraFieldOneEnabled()
    {
        return (int)$this->_getConfig(self::XML_PATH_FORM_ADDIF_FIELD_ONE_ENABLE);
    }
    
    /**
     * Get extra field one type
     *
     * @return string
     */
    public function getExtraFieldOneType()
    {
        return (string)$this->_getConfig(self::XML_PATH_FORM_ADDIF_FIELD_ONE_TYPE);
    }

    /**
     * Get extra field one label
     *
     * @return string
     */
    public function getExtraFieldOneLabel()
    {
        return (string)$this->_getConfig(self::XML_PATH_FORM_ADDIF_FIELD_ONE_LABEL);
    }

    /**
     * Get extra field one sort order
     *
     * @return int
     */
    public function getExtraFieldOneSortorder()
    {
        return (int)$this->_getConfig(self::XML_PATH_FORM_ADDIF_FIELD_ONE_ORDER);
    }

     /**
      * Check if extra field Two is enabled
      *
      * @return int
      */
    public function isExtraFieldTwoEnabled()
    {
        return (int)$this->_getConfig(self::XML_PATH_FORM_ADDIF_FIELD_TWO_ENABLE);
    }
    
    /**
     * Get extra field Two type
     *
     * @return string
     */
    public function getExtraFieldTwoType()
    {
        return (string)$this->_getConfig(self::XML_PATH_FORM_ADDIF_FIELD_TWO_TYPE);
    }

    /**
     * Get extra field Two label
     *
     * @return string
     */
    public function getExtraFieldTwoLabel()
    {
        return (string)$this->_getConfig(self::XML_PATH_FORM_ADDIF_FIELD_TWO_LABEL);
    }

    /**
     * Get extra field Two sort order
     *
     * @return int
     */
    public function getExtraFieldTwoSortorder()
    {
        return (int)$this->_getConfig(self::XML_PATH_FORM_ADDIF_FIELD_TWO_ORDER);
    }

    /**
     * Check if extra field Three is enabled
     *
     * @return int
     */
    public function isExtraFieldThreeEnabled()
    {
        return (int)$this->_getConfig(self::XML_PATH_FORM_ADDIF_FIELD_THREE_ENABLE);
    }
    
    /**
     * Get extra field Three type
     *
     * @return string
     */
    public function getExtraFieldThreeType()
    {
        return (string)$this->_getConfig(self::XML_PATH_FORM_ADDIF_FIELD_THREE_TYPE);
    }

    /**
     * Get extra field Three label
     *
     * @return string
     */
    public function getExtraFieldThreeLabel()
    {
        return (string)$this->_getConfig(self::XML_PATH_FORM_ADDIF_FIELD_THREE_LABEL);
    }

    /**
     * Get extra field Three sort order
     *
     * @return int
     */
    public function getExtraFieldThreeSortorder()
    {
        return (int)$this->_getConfig(self::XML_PATH_FORM_ADDIF_FIELD_THREE_ORDER);
    }

    /**
     * Check if extra field Four is enabled
     *
     * @return int
     */
    public function isExtraFieldFourEnabled()
    {
        return (int)$this->_getConfig(self::XML_PATH_FORM_ADDIF_FIELD_FOUR_ENABLE);
    }
    
    /**
     * Get extra field Four type
     *
     * @return string
     */
    public function getExtraFieldFourType()
    {
        return (string)$this->_getConfig(self::XML_PATH_FORM_ADDIF_FIELD_FOUR_TYPE);
    }

    /**
     * Get extra field Four label
     *
     * @return string
     */
    public function getExtraFieldFourLabel()
    {
        return (string)$this->_getConfig(self::XML_PATH_FORM_ADDIF_FIELD_FOUR_LABEL);
    }

    /**
     * Get extra field Four sort order
     *
     * @return int
     */
    public function getExtraFieldFourSortorder()
    {
        return (int)$this->_getConfig(self::XML_PATH_FORM_ADDIF_FIELD_FOUR_ORDER);
    }
 
    /**
     * Get spam block status
     *
     * @return string
     */
    public function getSpamBlockStatus()
    {
        return (string)$this->_getConfig(self::XML_PATH_FORM_SPAM_BLOCK_ENABLE);
    }
    
    /**
     * Check if recaptcha v2 checkbox is enabled
     *
     * @return bool
     */
    public function isRecaptchaV2CheckboxEnabled()
    {
        if ($this->getSpamBlockStatus() == 'google_recaptcha_v2_checkbox') {
            return true;
        }
        return false;
    }

    /**
     * Check if Magento CAPTCHA is enabled
     *
     * @return bool
     */
    public function isMagentoCaptchaEnabled()
    {
        if ($this->getSpamBlockStatus() == 'magento_captcha') {
            return true;
        }
        return false;
    }

    /**
     * Check if honeypot is enabled
     *
     * @return bool
     */
    public function isHoneypotEnabled()
    {
        if ($this->getSpamBlockStatus() == 'ulmod_honeypot') {
            return true;
        }
        return false;
    }

    /**
     * Get honeypot Not Allowed Message
     *
     * @return string
     */
    public function getHoneypotNotAllowedMessage()
    {
        return (string)$this->_getConfig(self::XML_PATH_FORM_HONEYPOT_NOT_ALLOWED_MSG);
    }
    
    /**
     * Get recaptcha form secret key config value
     *
     * @return string
     */
    public function getRecaptchaSecretKey()
    {
        return (string)$this->_getConfig(self::XML_PATH_FORM_RECAPTCHA_SECRET_KEY);
    }
    
    /**
     * Get recaptcha form site key config value
     *
     * @return string
     */
    public function getRecaptchaSiteKey()
    {
        return (string)$this->_getConfig(self::XML_PATH_FORM_RECAPTCHA_SITE_KEY);
    }

    /**
     * Is auto reply enabled
     *
     * @return bool
     */
    public function isAutoReplyEnabled()
    {
        return (bool)$this->_getConfig(self::XML_PATH_AUTOREPLY_ENABLE);
    }

    /**
     * Get auto reply template
     *
     * @return string
     */
    public function getAutoReplyTemplate()
    {
        return (string)$this->_getConfig(self::XML_PATH_AUTOREPLY_TEMPLATE);
    }

     /**
      * Get auto reply sender email
      *
      * @return string
      */
    public function getAutoReplySenderEmail()
    {
        $from = $this->_getConfig(self::XML_PATH_ADMIN_EMAIL_AUTOREPLY_SENDER_EMAIL);
        $result = $this->senderResolver->resolve($from);
        return $result['email'];
    }

    /**
     * Get auto reply sender name
     *
     * @return string
     */
    public function getAutoReplySenderName()
    {
        $from = $this->_getConfig(self::XML_PATH_ADMIN_EMAIL_AUTOREPLY_SENDER_EMAIL);
        $result = $this->senderResolver->resolve($from);
        return $result['name'];
    }
    
    /**
     * Get is notify admin config value
     *
     * @return bool
     */
    public function isAdminNotificationEnabled()
    {
        return (bool)$this->_getConfig(self::XML_PATH_ADMIN_EMAIL_ENABLED);
    }

    /**
     * Get sent message config value
     *
     * @return string
     */
    public function getSentMessage()
    {
        return (string)$this->_getConfig(self::XML_PATH_SENT_MESSAGE);
    }

    /**
     * Get is company enabled config value
     *
     * @return bool
     */
    public function getAdminNotificationSendFrom()
    {
        return (string)$this->_getConfig(self::XML_PATH_ADMIN_EMAIL_SEND_FROM);
    }
    
    /**
     * Get admin email config value
     *
     * @return bool
     */
    public function getAdminEmail()
    {
        return (string)$this->_getConfig(self::XML_PATH_ADMIN_EMAIL);
    }

    /**
     * Get admin email bcc config value
     *
     * @return string
     */
    public function getAdminEmailBcc()
    {
        return $this->_getConfig(self::XML_PATH_ADMIN_EMAIL_BCC);
    }
    
     /**
      * Get sender email
      *
      * @return string
      */
    public function getEmailSender()
    {
        $from = $this->_getConfig(self::XML_PATH_ADMIN_EMAIL_SENDER_EMAIL);
        $result = $this->senderResolver->resolve($from);
        return $result['email'];
    }

    /**
     * Get sender name
     *
     * @return string
     */
    public function getEmailSenderName()
    {
        $from = $this->_getConfig(self::XML_PATH_ADMIN_EMAIL_SENDER_EMAIL);
        $result = $this->senderResolver->resolve($from);
        return $result['name'];
    }
                        
    /**
     * Get email template config value
     *
     * @return bool
     */
    public function getAdminEmailTemplate()
    {
        return (string)$this->_getConfig(self::XML_PATH_ADMIN_EMAIL_TEMPLATE);
    }

    /**
     * Is privacy policy agreement enabled?
     *
     * @return bool
     */
    public function isPrivacyPolicyEnabled()
    {
        return $this->_getConfig(self::XML_PATH_FORM_PRIVACY_POLICY_FIELD_ENABLE);
    }

    /**
     * Get privacy policy agreement message
     *
     * @return string|mixed
     */
    public function getPrivacyPolicyMsg()
    {
        return $this->_getConfig(self::XML_PATH_FORM_PRIVACY_POLICY_FIELD_MSG);
    }

    /**
     * Get privacy policy agreement message filtered
     *
     * @return string
     */
    public function getPrivacyPolicyMsgFiltered()
    {
        $htmlContent = $this->filterProvider->getBlockFilter()
            ->filter($this->getPrivacyPolicyMsg());
        
        return $htmlContent;
    }
}
