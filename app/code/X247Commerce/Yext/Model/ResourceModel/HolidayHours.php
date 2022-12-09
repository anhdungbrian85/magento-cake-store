<?php

namespace X247Commerce\Yext\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb;

class HolidayHours extends AbstractDb
{
    public const TABLE_NAME = 'amasty_amlocator_holiday_hours';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, 'id');
    }
}
