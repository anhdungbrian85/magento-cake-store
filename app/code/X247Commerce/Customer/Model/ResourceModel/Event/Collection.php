<?php

namespace X247Commerce\Customer\Model\ResourceModel\Event;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Identifier field name for collection items
     *
     * Can be used by collections with items without defined
     *
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * Construct
     */
    protected function _construct()
    {
        $this->_init('\X247Commerce\Customer\Model\Event', '\X247Commerce\Customer\Model\ResourceModel\Event');
    }
}
