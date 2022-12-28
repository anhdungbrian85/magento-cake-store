<?php

namespace X247Commerce\Nutritics\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Config extends AbstractHelper
{
    const NUTRITICS_CONFIG_PATH_USERNAME = 'nutritics/api_settings/username';
    const NUTRITICS_CONFIG_PATH_PASSWORD = 'nutritics/api_settings/password';
    const NUTRITICS_CONFIG_PATH_USER_ID = 'nutritics/api_settings/user_id';
    const NUTRITICS_CONFIG_PATH_LIMIT = 'nutritics/api_settings/limit';
    
    const NUTRITICS_ENDPOINT = 'www.nutritics.com/api/v1.2';     

    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * Get Nutritics Account Username
     *
     * @return string|null
     */
    public function getNutriticsAccountUsername()
    {
        return $this->scopeConfig->getValue(self::NUTRITICS_CONFIG_PATH_USERNAME, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get Nutritics Account Password
     *
     * @return string|null
     */
    public function getNutriticsAccountPassword()
    {
        return $this->scopeConfig->getValue(self::NUTRITICS_CONFIG_PATH_PASSWORD, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get Limit To Fetch
     *
     * @return int
     */
    public function getLimit()
    {
        return $this->scopeConfig->getValue(self::NUTRITICS_CONFIG_PATH_LIMIT, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get User Id (the ID of the Nutritics user (your developer account can be granted access to multiple Nutritics users) who's objects you would like to work with)
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->scopeConfig->getValue(self::NUTRITICS_CONFIG_PATH_USER_ID, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get Nutritics Base Api EndPoint
     *
     * @return string
     */
    public function getBaseApiEndPointUrl()
    {   
        return self::NUTRITICS_ENDPOINT;
    }   
}