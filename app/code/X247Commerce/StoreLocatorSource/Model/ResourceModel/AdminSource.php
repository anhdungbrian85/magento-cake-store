<?php
namespace X247Commerce\StoreLocatorSource\Model\ResourceModel;
 
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
 
class AdminSource extends AbstractDb
{
    protected function _construct()
    {        
        $this->_init('admin_user_source_link', 'entity_id');
    }
}