<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace X247Commerce\CustomerAddressAutocomplete\Helper;

/**
 * Data helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_GOOGLE_API_KEY = 'x247commerce_address_autocomplete/general/google_api_key';
    const XML_PATH_ALLOWED_SPECIFIC = 'x247commerce_address_autocomplete/general/allowspecific';
    const XML_PATH_SPECIFIC_COUNTRIES = 'x247commerce_address_autocomplete/general/specificcountry';

    /**
     * Construct
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
    }

    public function getGoogleApiKey() 
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_GOOGLE_API_KEY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }


    public function getAllowedCountries()
    {
        if (!$this->scopeConfig->getValue(self::XML_PATH_ALLOWED_SPECIFIC)) {
            return false;
        }  
        return $this->scopeConfig->getValue(
            self::XML_PATH_SPECIFIC_COUNTRIES,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
}
