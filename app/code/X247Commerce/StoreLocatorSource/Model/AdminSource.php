<?php

namespace X247Commerce\StoreLocatorSource\Model;

use Magento\Framework\Model\AbstractModel;

class AdminSource extends AbstractModel
{
    
    protected function _construct()
    {
        $this->_init('X247Commerce\StoreLocatorSource\Model\ResourceModel\AdminSource');
    }
}
