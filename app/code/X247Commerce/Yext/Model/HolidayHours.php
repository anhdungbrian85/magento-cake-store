<?php

namespace X247Commerce\Yext\Model;

/**
 * Class Schedule
 */
class HolidayHours extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init(\X247Commerce\Yext\Model\ResourceModel\HolidayHours::class);
        $this->setIdFieldName('id');
    }
}
