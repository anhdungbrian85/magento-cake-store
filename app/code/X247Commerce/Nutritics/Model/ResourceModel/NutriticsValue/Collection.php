<?php

namespace X247Commerce\Nutritics\Model\ResourceModel\NutriticsValue;

/**
 * Class Collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            \X247Commerce\Nutritics\Model\NutriticsValue::class,
            \X247Commerce\Nutritics\Model\ResourceModel\NutriticsValue::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}