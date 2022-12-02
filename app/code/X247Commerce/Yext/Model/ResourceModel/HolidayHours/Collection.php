<?php

namespace X247Commerce\Yext\Model\ResourceModel\HolidayHours;

/**
 * Class Collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            \X247Commerce\Yext\Model\HolidayHours::class,
            \X247Commerce\Yext\Model\ResourceModel\HolidayHours::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
