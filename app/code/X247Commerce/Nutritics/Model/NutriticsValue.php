<?php

namespace X247Commerce\Nutritics\Model;

class NutriticsValue extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init(\X247Commerce\Nutritics\Model\ResourceModel\NutriticsValue::class);
        $this->setIdFieldName('entity_id');
    }
}