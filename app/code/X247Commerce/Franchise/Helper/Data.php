<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace X247Commerce\Franchise\Helper;

use Magento\Store\Model\ScopeInterface;
/**
 * Data helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_FRANCHISE_EMAIL = 'x247commerce_franchise/general/franchise_email';
    const XML_PATH_EMAIL_SENDER = 'trans_email/ident_support/email';
    const XML_PATH_NAME_SENDER = 'trans_email/ident_support/name';

    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
    }

    public function getEmailFranchise(){
        return $this->getConfigValue(self::XML_PATH_FRANCHISE_EMAIL);
    }

    public function getEmailSender()
    {
        return [
            'name' => $this->getConfigValue(self::XML_PATH_NAME_SENDER),
            'email' => $this->getConfigValue(self::XML_PATH_EMAIL_SENDER)
        ];
    }

    public function getConfigValue($path)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

}
