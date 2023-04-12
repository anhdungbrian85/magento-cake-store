<?php

namespace X247Commerce\Checkout\Plugin\Checkout\DeliveryDate;

class ConfigProvider
{
  	public const DEFAULT_DELIVERY_TIMESLOT = '16:00 - 20:00';

    public function afterGetDeliveryHours(
        \Amasty\CheckoutDeliveryDate\Model\ConfigProvider $subject,
        $result
    ) {
        return $options = [
        	[
            	'value' => '-1',
            	'label' => ' ',
	        ],
	        [
	        	'value' => '16',
            	'label' => self::DEFAULT_DELIVERY_TIMESLOT,
	        ]
	    ];
    }
}
