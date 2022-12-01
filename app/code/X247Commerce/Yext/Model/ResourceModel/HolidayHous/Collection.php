<?php

namespace X247Commerce\Yext\Model\ResourceModel\HolidayHous;

/**
 * Class Collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            \Amasty\Storelocator\Model\HolidayHous::class,
            \Amasty\Storelocator\Model\ResourceModel\HolidayHous::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}
