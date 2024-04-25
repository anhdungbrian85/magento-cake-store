<?php
namespace X247Commerce\SmsTextManagement\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class TimeOptions implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('UTC + 1')],
            ['value' => 1, 'label' => __('UTC + 0')]
        ];
    }
}
