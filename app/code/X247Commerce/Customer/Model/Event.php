<?php

namespace X247Commerce\Customer\Model;

class Event extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('\X247Commerce\Customer\Model\ResourceModel\Event');
    }
}
