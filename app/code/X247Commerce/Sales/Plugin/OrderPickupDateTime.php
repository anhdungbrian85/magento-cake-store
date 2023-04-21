<?php 

namespace X247Commerce\Sales\Plugin;

class OrderPickupDateTime
{

    public function afterGetTime(
        \Amasty\StorePickupWithLocator\Block\Adminhtml\Sales\Order\DateTime $subject,
        $result
    )
    {
    	if ($result) {
			return explode(' - ', $result)[0];
		}
		return $result;
    }
}