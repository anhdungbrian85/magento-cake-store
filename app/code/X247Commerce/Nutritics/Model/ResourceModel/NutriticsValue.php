<?php

namespace X247Commerce\Nutritics\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb;

class NutriticsValue extends AbstractDb
{
    public const TABLE_NAME = 'nutritics_product_attribute_value';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, 'entity_id');
    }
}