<?php

namespace X247Commerce\Checkout\Plugin\Block\Cart;

use Magento\Framework\Stdlib\ArrayManager;

class LayoutProcessor 
{

    public function afterProcess(\Amasty\StorePickupWithLocator\Block\Cart\LayoutProcessor $subject, $result)
    {
        if (isset($result['components']['checkout']['children']['steps']['children']['shipping-step'])) {
            //checkout
            $amStorePickup = $result['components']['checkout']['children']['steps']['children']
                ['shipping-step']['children']['shippingAddress']['children']['amstorepickup'];
            $result['components']['checkout']['children']['sidebar']['children']
                ['block-store-locator']['children'][] = $amStorePickup;
            unset($result['components']['checkout']['children']['steps']['children']
                ['shipping-step']['children']['shippingAddress']['children']['amstorepickup']);
        } elseif (isset($jsLayout['components']['block-summary']['children']['block-rates'])) {
            unset($result['components']['block-summary']['children']['block-rates']['children']['amstorepickup']);
        }
        return $result;
    }
}
