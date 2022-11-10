<?php

namespace X247Commerce\Checkout\Plugin\Block\Cart;

use Magento\Framework\Stdlib\ArrayManager;

class LayoutProcessor 
{

    public function afterProcess(\Amasty\StorePickupWithLocator\Block\Cart\LayoutProcessor $subject, $result)
    {
        unset($result['components']['block-summary']['children']['block-rates']['children']['amstorepickup']);
        return $result;
    }
}
