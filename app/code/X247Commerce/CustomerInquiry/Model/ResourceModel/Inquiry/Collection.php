<?php
namespace X247Commerce\CustomerInquiry\Model\ResourceModel\Inquiry;
 
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
 
class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            'X247Commerce\CustomerInquiry\Model\Inquiry',
            'X247Commerce\CustomerInquiry\Model\ResourceModel\Inquiry'
        );
    }
}