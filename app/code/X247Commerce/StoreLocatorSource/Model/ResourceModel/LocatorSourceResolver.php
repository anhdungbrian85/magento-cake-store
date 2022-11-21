<?php
/**
 * @author Phung Thong <phung.thong@247commerce.co.uk>
 * @package   X247Commerce/StoreLocatorSource
 * @copyright 2022 247Commerce
 */

namespace X247Commerce\StoreLocatorSource\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use X247Commerce\StoreLocatorSource\Model\ResourceModel\AdminSource\CollectionFactory as AdminSourceCollectionFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\InventoryApi\Api\SourceRepositoryInterface;

class LocatorSourceResolver
{
    protected ResourceConnection $resource;
    protected $connection;
    protected AdminSourceCollectionFactory $sourceLink;
    protected SearchCriteriaBuilder $searchCriteriaBuilder;
    protected SourceRepositoryInterface $sourceRepository;

    public function __construct(
        ResourceConnection $resource,
        AdminSourceCollectionFactory $sourceLink,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SourceRepositoryInterface $sourceRepository
    )
    {        
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->sourceLink = $sourceLink;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sourceRepository = $sourceRepository;
    }

    public function getAmLocatorBySource($sourceCode)
    {
        $sourceTbl = $this->resource->getTableName('inventory_source');
        $sqlQuery = $this->connection->select()
                ->from($sourceTbl, ['amlocator_store'])
                ->where("source_code = ?", $sourceCode);
        return $this->connection->fetchOne($sqlQuery);
    }

    public function getSourcesByUser($user)
    {
        $userId = $user->getId();
        $sourcesCol = $this->sourceLink->create()->addFieldToFilter('user_id', $userId);
        $result = [];
        foreach($sourcesCol as $sourceUser) {
            $result[] = $sourceUser->getData('source_code');
        }
        return $result ? : false;
    }

    public function getAmLocatorStoresByUser($user)
    {
        $sources = $this->getSourcesByUser($user);
        $sourceTbl = $this->resource->getTableName('inventory_source');

        if ($sources) {
            $sqlGetSources = $this->connection->select()
                ->from($sourceTbl, ['amlocator_store'])
                ->where("source_code IN (?)", $sources);
            
            $amStores = $this->connection->fetchAll($sqlGetSources);
            $resultAmStoreIds = [];
            foreach($amStores as $amStore) {
                if (!empty($amStore['amlocator_store'])) {
                    $resultAmStoreIds[] = $amStore['amlocator_store'];
                }
            }
            return $resultAmStoreIds;
        }
        return false;
    }
}