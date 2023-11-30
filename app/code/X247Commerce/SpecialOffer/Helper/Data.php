<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace X247Commerce\SpecialOffer\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;


/**
 * Sales admin helper.
 */
class Data extends AbstractHelper
{
    CONST XML_PATH_ENABLED_SPECIAL_OFFER = 'special_offer/general/enable';
    CONST XML_PATH_SPECIAL_SKU = 'special_offer/general/product_sku';
    CONST XML_PATH_COUPON_CODE = 'special_offer/general/coupon_code';
    CONST XML_PATH_SUCCESS_MESSAGE = 'special_offer/popup/message';


    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * @return mixed
     */
    public function isEnable()
    {
        return  $this->scopeConfig->getValue(self::XML_PATH_ENABLED_SPECIAL_OFFER, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getOfferProductSku()
    {
        return  $this->scopeConfig->getValue(self::XML_PATH_SPECIAL_SKU, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getSuccessCartMessage()
    {
        return  $this->scopeConfig->getValue(self::XML_PATH_SUCCESS_MESSAGE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getSpecialCoupon()
    {
        return  $this->scopeConfig->getValue(self::XML_PATH_COUPON_CODE, ScopeInterface::SCOPE_STORE);
    }
}
