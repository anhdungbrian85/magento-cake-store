<?php
namespace X247Commerce\CustomerInquiry\Model;
 
use Magento\Framework\Model\AbstractModel;
 
class Inquiry extends AbstractModel{
    protected function _construct()
    {
        $this->_init('X247Commerce\CustomerInquiry\Model\ResourceModel\Inquiry');
    }
}