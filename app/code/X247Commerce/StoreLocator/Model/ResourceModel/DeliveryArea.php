<?php

namespace X247Commerce\StoreLocator\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb;

class DeliveryArea extends AbstractDb
{
    public const TABLE_NAME = 'store_location_delivery_area';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, 'id');
    }
}