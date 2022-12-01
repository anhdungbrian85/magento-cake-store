<?php

namespace X247Commerce\Yext\Model;

/**
 * Class Schedule
 */
class HolidayHous extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init(\Amasty\Storelocator\Model\ResourceModel\HolidayHous::class);
        $this->setIdFieldName('id');
    }
}
