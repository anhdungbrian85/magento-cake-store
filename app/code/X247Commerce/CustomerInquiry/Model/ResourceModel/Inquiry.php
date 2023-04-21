<?php
namespace X247Commerce\CustomerInquiry\Model\ResourceModel;
 
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
 
class Inquiry extends AbstractDb
{
    protected function _construct()
    {
        
        $this->_init('customer_inquiry', 'entity_id');
    }
}