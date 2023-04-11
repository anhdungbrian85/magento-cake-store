<?php

namespace X247Commerce\Checkout\Plugin\Checkout\DeliveryDate;

use X247Commerce\Checkout\Plugin\Checkout\DeliveryDate\ConfigProvider;

class DeliveryInfo
{
  	

    public function afterGetDeliveryFields(
        \Amasty\CheckoutDeliveryDate\Block\Sales\Order\Info\Delivery $subject,
        $result
    ) {
        
    	foreach ($result as &$deliveryInfo) {
    		if ($deliveryInfo['label'] == 'Delivery Time') {
    			$deliveryInfo['value'] = ConfigProvider::DEFAULT_DELIVERY_TIMESLOT;
    		}
    	}
        return $result;
    }
}
