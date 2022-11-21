<?php

namespace X247Commerce\Yext\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

/**
 * Class Config
 *
 * @package Avento\Checkout\Helper
 */
class YextHelper extends AbstractHelper
{

    public function __construct(
        Context $context
    ) {
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

}
