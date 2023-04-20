<?php

namespace X247Commerce\StoreLocator\Model\ResourceModel\DeliveryArea;

/**
 * Class Collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init(
            \X247Commerce\StoreLocator\Model\DeliveryArea::class,
            \X247Commerce\StoreLocator\Model\ResourceModel\DeliveryArea::class
        );
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }
}