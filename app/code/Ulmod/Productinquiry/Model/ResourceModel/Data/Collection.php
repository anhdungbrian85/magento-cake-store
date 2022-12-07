<?php
/*** Copyright © Ulmod. All rights reserved. **/
 
namespace Ulmod\Productinquiry\Model\ResourceModel\Data;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Store\Model\Store as StoreModel;
use Magento\Framework\DB\Select as DBSelect;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
        
class Collection extends AbstractCollection
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    
    /**
     * @var string
     */
    protected $_idFieldName = 'inquiry_id';

    /**
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param StoreManagerInterface $storeManager
     * @param AdapterInterface|null $connection
     * @param AbstractDb|null $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
        $this->storeManager = $storeManager;
    }
    
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Ulmod\Productinquiry\Model\Data::class,
            \Ulmod\Productinquiry\Model\ResourceModel\Data::class
        );
        $this->_map['fields']['store'] = 'store_table.store_id';
    }
 
    /**
     * Perform adding filter by store
     *
     * @param int|array|StoreModel $store
     * @param bool $withAdmin
     * @return void
     */
    protected function performAddStoreFilter($store, $withAdmin = true)
    {
        if ($store instanceof StoreModel) {
            $store = [$store->getId()];
        }
        if (!is_array($store)) {
            $store = [$store];
        }
        if ($withAdmin) {
            $store[] = StoreModel::DEFAULT_STORE_ID;
        }
        $this->addFilter(
            'store',
            ['in' => $store],
            'public'
        );
    }
    
    /**
     * Perform operations after collection load
     *
     * @param string $tableName
     * @param string $linkField
     * @return void
     */
    protected function performAfterLoad($tableName, $linkField)
    {
        $inqItems = $this->getColumnValues($linkField);
        if (count($inqItems)) {
            $connection = $this->getConnection();
            $select = $connection->select()
                ->from(
                    ['ulmod_productinquiry_store' => $this->getTable($tableName)]
                )
                ->where(
                    'ulmod_productinquiry_store.' . $linkField . ' IN (?)',
                    $inqItems
                );
            
            $result = $connection->fetchPairs($select);
            if ($result) {
                foreach ($this as $inqItem) {
                    $entityId = $inqItem->getData($linkField);
                    if (!isset($result[$entityId])) {
                        continue;
                    }
                    if ($result[$entityId] == 0) {
                        $stores = $this->storeManager
                            ->getStores(false, true);
                        $storeId = current($stores)
                            ->getId();
                        $storeCode = key($stores);
                    } else {
                        $storeId = $result[
                            $inqItem->getData($linkField)
                        ];
                        $storeCode = $this->storeManager
                            ->getStore($storeId)->getCode();
                    }
                
                    $inqItem->setData(
                        'store_code',
                        $storeCode
                    );
                    
                    $inqItem->setData(
                        '_first_store_id',
                        $storeId
                    );
                    
                    $inqItem->setData(
                        'store_id',
                        [$result[$entityId]]
                    );
                }
            }
        }
    }
   
    /**
     * Add filter by store
     *
     * @param int|array|StoreModel $store
     * @param bool $withAdmin
     * @return $this
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        $this->performAddStoreFilter($store, $withAdmin);
        
        return $this;
    }

    /**
     * Add field to filter to collection
     *
     * @param array|string $fieldFilter
     * @param string|int|array|null $condition
     * @return $this
     */
    public function addFieldToFilter($fieldFilter, $condition = null)
    {
        if ($fieldFilter === 'store_id') {
            return $this->addStoreFilter(
                $condition,
                false
            );
        }
        
        return parent::addFieldToFilter(
            $fieldFilter,
            $condition
        );
    }
    
    /**
     * Join store relation table if there is store filter
     *
     * @param string $tableName
     * @param string $linkField
     * @return void
     */
    protected function joinStoreRelationTable(
        $tableName,
        $linkField
    ) {
        $table = $this->getTable($tableName);
        if ($this->getFilter('store')) {
            $this->getSelect()->join(
                ['store_table' => $this->getTable($tableName)],
                'main_table.' . $linkField
                . ' = store_table.' . $linkField,
                []
            )->group(
                'main_table.' . $linkField
            );
        }
        
        parent::_renderFiltersBefore();
    }
   
    /**
     * Perform operations after collection load
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $this->performAfterLoad(
            'ulmod_productinquiry_store',
            'inquiry_id'
        );
        
        return parent::_afterLoad();
    }

    /**
     * Get SQL for get record count
     *
     * Extra GROUP BY strip added.
     *
     * @return DBSelect
     */
    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(DBSelect::GROUP);
        
        return $countSelect;
    }
    
    /**
     * Join store relation table if there is store filter
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        $this->joinStoreRelationTable(
            'ulmod_productinquiry_store',
            'inquiry_id'
        );
    }
    
    /**
     * Filter collection by status
     *
     * @param int $status
     * @return $this
     */
    public function addStatusFilter($status)
    {
        $this->getSelect()
            ->where('main_table.status = ?', $status);
            
        return $this;
    }

    /**
     * Filter collection by rating
     *
     * @return $this
     */
    public function addRatingFilter()
    {
        $this->getSelect()
            ->where('main_table.rating > 0');
            
        return $this;
    }
    
    /**
     * Filter collection by use in widget
     *
     * @param int|string $widget
     * @return $this
     */
    public function addWidgetFilter($widget)
    {
        $this->getSelect()->where(
            'main_table.widget = ?',
            $widget
        );
            
        return $this;
    }
}
