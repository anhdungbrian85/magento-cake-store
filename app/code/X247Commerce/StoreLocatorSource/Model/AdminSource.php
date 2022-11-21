<?php

namespace X247Commerce\StoreLocatorSource\Model;

use Magento\Framework\Model\AbstractModel;

class AdminSource extends AbstractModel
{
    
    protected function _construct()
    {
        $this->_init('X247Commerce\StoreLocatorSource\Model\ResourceModel\AdminSource');
    }

    public function getAssignedSources($userId)
    {
        $collection = $this->create()->getCollection()->addFieldToFilter('user_id', ['eq' => $userId]);
        $data = [];
        if ($collection) {
            foreach ($collection as $item) {
                $data[] = $item->getSourceCode();
            }
        }
     
        return $data;     
    }
}
