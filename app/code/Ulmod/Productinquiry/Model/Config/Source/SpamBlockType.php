<?php
/*** Copyright © Ulmod. All rights reserved. **/
 
namespace Ulmod\Productinquiry\Model\Config\Source;

class SpamBlockType implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'no',
                'label' => __('No')
            ],
            [
                'value' => 'google_recaptcha_v2_checkbox',
                'label' => __('Yes, reCAPTCHA v2 (“I am not a robot”) by Google')
            ],
            [
                'value' => 'magento_captcha',
                'label' => __('Yes, CAPTCHA by Magento')
            ],
            [
                'value' => 'ulmod_honeypot',
                'label' => __('Yes, Honeypot')
            ],
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $array = [];
        foreach ($this->toOptionArray() as $item) {
            $array[$item['value']] = $item['label'];
        }
        return $array;
    }
}
