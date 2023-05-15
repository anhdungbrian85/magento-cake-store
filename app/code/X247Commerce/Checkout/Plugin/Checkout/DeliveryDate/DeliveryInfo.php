<?php

namespace X247Commerce\Checkout\Plugin\Checkout\DeliveryDate;

use X247Commerce\Checkout\Model\Config\DeliveryConfigProvider;

class DeliveryInfo
{
    protected DeliveryConfigProvider $deliveryConfigProvider;

    public function __construct(
        DeliveryConfigProvider $deliveryConfigProvider
    ) {
        $this->deliveryConfigProvider = $deliveryConfigProvider;
    }
    public function afterGetDeliveryFields(
        \Amasty\CheckoutDeliveryDate\Block\Sales\Order\Info\Delivery $subject,
        $result
    ) {
        foreach ($result as $deliveryInfo) {
            if ($deliveryInfo['label'] == __('Delivery Time')) {
                $isWeekendTimeSlot = (int)(explode(':', trim(explode( '-', $deliveryInfo['value'])[0]))[0])  == \X247Commerce\Checkout\Model\Config\DeliveryConfigProvider::WEEKEND_DELIVERY_TIME_START;
            }
        }

    	foreach ($result as &$deliveryInfo) {
    		if ($deliveryInfo['label'] ==  __('Delivery Time') ) {
    			$deliveryInfo['value'] = $this->deliveryConfigProvider->getDeliveryHours(null, $isWeekendTimeSlot)[1]['label'];
    		}
    	}
        return $result;
    }


}
