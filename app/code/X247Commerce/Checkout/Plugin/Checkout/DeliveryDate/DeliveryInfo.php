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
            if ($deliveryInfo['label'] == __('Delivery Date')) {
                $deliveryDate = new \DateTime($deliveryInfo['value']);
                $dayOfWeek = $deliveryDate->format('N');
                $isWeekend = $dayOfWeek == 6 || $dayOfWeek == 7;
            }
        }

    	foreach ($result as &$deliveryInfo) {
    		if ($deliveryInfo['label'] ==  __('Delivery Time') ) {
    			$deliveryInfo['value'] = $this->deliveryConfigProvider->getDeliveryHours(null, $isWeekend)[1]['label'];
    		}
    	}
        return $result;
    }


}
