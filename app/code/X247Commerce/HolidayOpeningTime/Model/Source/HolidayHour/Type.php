<?php

namespace X247Commerce\HolidayOpeningTime\Model\Source\HolidayHour;

class Type implements \Magento\Framework\Option\ArrayInterface
{
    const OPEN_VALUE = 0;
    const SPLIT_VALUE = 1;
    const FULL_TIME_VALUE = 2;
    const CLOSED_VALUE = 3;
    const REGULAR_HOURS_VALUE = 4;

    public function toOptionArray()
    {
        return [
            [
                'label' => __('Open'),
                'value' => self::OPEN_VALUE,
            ],
            [
                'label' => __('Split'),
                'value' => self::SPLIT_VALUE,
            ],
            [
                'label' => __('24 Hours'),
                'value' => self::FULL_TIME_VALUE,
            ],
            [
                'label' => __('Closed'),
                'value' => self::CLOSED_VALUE,
            ],
            [
                'label' => __('Regular Hours'),
                'value' => self::REGULAR_HOURS_VALUE,
            ],
        ];
    }
}
