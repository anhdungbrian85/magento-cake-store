<?php
namespace X247Commerce\StoreLocatorSource\Model\ResourceModel\AdminSource;
 
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
 
class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            'X247Commerce\StoreLocatorSource\Model\AdminSource',
            'X247Commerce\StoreLocatorSource\Model\ResourceModel\AdminSource'
        );
    }
}