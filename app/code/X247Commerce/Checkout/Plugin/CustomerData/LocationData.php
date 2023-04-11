<?php
namespace X247Commerce\Checkout\Plugin\CustomerData;

class LocationData
{
    public function aroundGetSectionData(\Amasty\StorePickupWithLocator\CustomerData\LocationData $subject, callable $proceed)
    {
        return ['stores' => []];
    }
}
