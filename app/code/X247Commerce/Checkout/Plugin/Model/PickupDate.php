<?php
namespace X247Commerce\Checkout\Plugin\Model;

class PickupDate
{
    public function afterGetDateFormat(\Amasty\StorePickupWithLocator\Model\PickupDate $subject)
    {
        return 'dd/mm/yy';
    }
}