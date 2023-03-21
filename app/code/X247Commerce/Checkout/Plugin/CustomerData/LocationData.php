<?php
namespace X247Commerce\Checkout\Plugin\CustomerData;

class LocationData
{
    public function afterGetSectionData(
    	\Amasty\StorePickupWithLocator\CustomerData\LocationData $subject, 
    	$result
    ) {
        return ['stores' => []];;
    }
}
