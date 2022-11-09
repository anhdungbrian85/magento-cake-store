<?php
/**
 * Yext API config helper
 *
 * @author     Phung Thong <phung.thong@247commerce.co.uk>
 * @copyright  2022 247Commerce
 */

namespace X247Commerce\Yext\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Config
 *
 * @package Avento\Checkout\Helper
 */
class Config extends AbstractHelper
{
    /**#@+
     * Constants for config path.
     */
    const YEXT_CONFIG_PATH_ACCOUNT_ID = 'yext/api_settings/account_id';
    const YEXT_CONFIG_PATH_API_KEY = 'yext/api_settings/key';
    const YEXT_CONFIG_PATH_API_MODE = 'yext/api_settings/mode';
    const YEXT_CONFIG_PATH_API_TYPE = 'yext/api_settings/type';

    const YEXT_CONFIG_LIVE_MODE = 1;
    const YEXT_CONFIG_SANDBOX_MODE = 2;

    const YEXT_CONFIG_KNOWNLEADGE_TYPE = 1;
    const YEXT_CONFIG_LIVE_TYPE = 2;
    
    const YEXT_ENDPOINT_SANDBOX_MODE = [
        /** @todo find sandbox mode-live type base url */
        1 => 'https://api-sandbox.yext.com/',  // Knownleadge
        2 => 'https://api-sandbox.yext.com/',  // Live  

    ]; 

    const YEXT_ENDPOINT_LIVE_MODE = [
        1 => 'https://api.yext.com/',  // Knownleadge
        2 => 'https://liveapi.yext.com/', // Live 

    ]; 



    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * Get Yext Account Id
     *
     * @return string|null
     */
    public function getYextAccountId()
    {
        return $this->scopeConfig->getValue(self::YEXT_CONFIG_PATH_ACCOUNT_ID, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get Yext API key
     *
     * @return string|null
     */
    public function getYextApiKey()
    {
        return $this->scopeConfig->getValue(self::YEXT_CONFIG_PATH_API_KEY, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get Yext API Mode
     *
     * @return string|null
     */
    public function getYextApiMode()
    {
        return $this->scopeConfig->getValue(self::YEXT_CONFIG_PATH_API_MODE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get Yext API Type
     *
     * @return string|null
     */
    public function getYextApiType()
    {
        return $this->scopeConfig->getValue(self::YEXT_CONFIG_PATH_API_TYPE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get Yext base API endpoint url
     *
     * @return string
     */
    public function getBaseApiEndPointUrl()
    {
        $mode = $this->getYextApiMode() ? : self::YEXT_CONFIG_SANDBOX_MODE;
        $type = $this->getYextApiType() ? : self::YEXT_CONFIG_KNOWNLEADGE_TYPE;
        
        return ($mode == self::YEXT_CONFIG_LIVE_MODE) ? 
            self::YEXT_ENDPOINT_LIVE_MODE[$type] : self::YEXT_ENDPOINT_SANDBOX_MODE[$type];
    }   

}
