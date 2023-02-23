<?php

namespace X247Commerce\KlaviyoSMS\Plugin\Block\Checkout;

class LayoutProcessor
{
    public function afterProcess(\Magento\Checkout\Block\Checkout\LayoutProcessor $processor, $jsLayout)
    {
        $klSmsConsentComponent = $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['before-form']['children']['kl_sms_consent'];
        $klSmsPhoneNumber = $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['before-form']['children']['kl_sms_phone_number'];
        echo "<pre>";
        var_dump($jsLayout);
        die;

        return $jsLayout;
    }
}
