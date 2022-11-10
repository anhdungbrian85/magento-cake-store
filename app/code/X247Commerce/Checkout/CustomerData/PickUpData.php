<?php

namespace X247Commerce\Checkout\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Framework\UrlInterface;
use X247Commerce\Checkout\Api\StoreLocationContextInterface;

class PickUpData implements SectionSourceInterface
{

    protected $storeLocationContextInterface;

    public function __construct(
        StoreLocationContextInterface $storeLocationContextInterface
    ) {
        $this->storeLocationContextInterface = $storeLocationContextInterface;
    }

    /**
     * @return array
     */
    public function getSectionData()
    {
        return [
            'am_pickup_store' => 8
        ];
        
        return [];
        
    }
}
