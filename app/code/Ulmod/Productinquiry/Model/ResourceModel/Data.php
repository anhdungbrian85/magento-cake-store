<?php
/*** Copyright © Ulmod. All rights reserved. **/
 
namespace Ulmod\Productinquiry\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\Store as StoreModel;

/**
 * Productinquiry data mysql resource
 */
class Data extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;
    
    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param string|null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $resourcePrefix = null
    ) {
        parent::__construct($context, $resourcePrefix);
        $this->date = $date;
        $this->storeManager = $storeManager;
    }
    
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ulmod_productinquiry_data', 'inquiry_id');
    }
    
    /**
     * Process inquiry data before deleting
     *
     * @param AbstractModel $object
     * @return \Ulmod\Productinquiry\Model\ResourceModel\Data
     */
    protected function _beforeDelete(AbstractModel $object)
    {
        $condition = ['inquiry_id = ?' => (int)$object->getId()];
        $this->getConnection()->delete(
            $this->getTable('ulmod_productinquiry_store'),
            $condition
        );
        
        return parent::_beforeDelete($object);
    }
    
    /**
     * Process inquiry data before saving
     *
     * @param AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeSave(AbstractModel $object)
    {
        if (!$object->getId() || !$object->getDate()) {
            $object->setDate($this->date->gmtDate());
        }
        
        return parent::_beforeSave($object);
    }
    /**
     * Perform operations after object save
     *
     * @param AbstractModel $object
     * @return $this
     */
    protected function _afterSave(AbstractModel $object)
    {
        $oldStores = $this->lookupStoreIds($object->getId());
        $newStores = (array)$object->getStores();
        
        $table = $this->getTable('ulmod_productinquiry_store');

        $delete = array_diff($oldStores, $newStores);
        if ($delete) {
            $where = [
                'inquiry_id = ?' => (int)$object->getId(),
                'store_id IN (?)' => $delete
            ];
            $this->getConnection()->delete($table, $where);
        }
        
        $insert = array_diff($newStores, $oldStores);
        if ($insert) {
            $data = [];
            foreach ($insert as $storeId) {
                $data[] = [
                    'inquiry_id' => (int)$object->getId(),
                    'store_id' => (int)$storeId
                ];
            }
            
            $this->getConnection()->insertMultiple($table, $data);
        }
        
        return parent::_afterSave($object);
    }
  
    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $id
     * @return array
     */
    public function lookupStoreIds($id)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from(
                $this->getTable('ulmod_productinquiry_store'),
                'store_id'
            )->where(
                'inquiry_id = :inquiry_id'
            );
        $binds = [':inquiry_id' => (int)$id];
        
        return $connection->fetchCol($select, $binds);
    }
    
    /**
     * Perform operations after object load
     *
     * @param AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(AbstractModel $object)
    {
        if ($object->getId()) {
            $stores = $this->lookupStoreIds($object->getId());
            $object->setData('store_id', $stores);
            $object->setData('stores', $stores);
        }
        
        return parent::_afterLoad($object);
    }
    
    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param \Ulmod\Productinquiry\Model\Data $object
     * @return \Magento\Framework\DB\Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        if ($object->getStoreId()) {
            $stores = [
                (int)$object->getStoreId(),
                StoreModel::DEFAULT_STORE_ID
            ];
            $select->join(
                ['tts' => $this->getTable('ulmod_productinquiry_store')],
                $this->getMainTable() . '.inquiry_id = tts.inquiry_id',
                ['store_id']
            )->where(
                'tts.store_id in (?)',
                $stores
            )->order(
                'store_id DESC'
            )->limit(
                1
            );
        }
        
        return $select;
    }
}
