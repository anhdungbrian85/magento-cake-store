<?php

namespace X247Commerce\StoreLocator\Model;

class DeliveryArea extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init(\X247Commerce\StoreLocator\Model\ResourceModel\DeliveryArea::class);
        $this->setIdFieldName('id');
    }
}