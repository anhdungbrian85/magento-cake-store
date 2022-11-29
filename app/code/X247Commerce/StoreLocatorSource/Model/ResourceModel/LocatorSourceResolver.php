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

    /**
     * Get amasty_amlocator_location.id
     * @param $sourceCode string
     * @return string|null
     * 
     **/

    public function getAmLocatorBySource($sourceCode)
    {
        $sourceTbl = $this->resource->getTableName('inventory_source');
        $sqlQuery = $this->connection->select()
                ->from($sourceTbl, ['amlocator_store'])
                ->where("source_code = ?", $sourceCode);
        return $this->connection->fetchOne($sqlQuery);
    }

    /**
     * Get source_code by amlocator_store id
     * @param $locationId
     * @return [source_code]|null
     * 
     **/
    public function getSourceCodeByAmLocator($locationId)
    {
        $sourceTbl = $this->resource->getTableName('inventory_source');
        $sqlQuery = $this->connection->select()
                ->from($sourceTbl, ['source_code'])
                ->where("amlocator_store = ?", $locationId);
        $results = $this->connection->fetchAll($sqlQuery);
        $resultSource = [];
        foreach($results as $result) {
            if (!empty($result['source_code'])) {
                $resultSource[] = $result['source_code'];
            }
        }
        return $resultSource;
    }

    /**
     * Get all source code by user
     * @param $user Magento\User\Model\User
     * @return [source_code]
     * 
     **/
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

    /**
     * Get all users by source code
     * @param sourceCode string
     * @return [Magento\User\Model\User]
     * 
     **/
    public function getUserBySource($sourceCode)
    {
        $userId = $user->getId();
        $sourcesCol = $this->sourceLink->create()->addFieldToFilter('source_code', $sourceCode);
        $result = [];
        foreach($sourcesCol as $sourceUser) {
            $result[] = $sourceUser->getData('user_id');
        }
        return $result ? : false;
    }

    /**
     * Get all store locations by User
     * @param $user Magento\User\Model\User
     * @return [amasty_amlocator_location.id]|false
     * 
     **/
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
    /**
     * Get User by AmLocation Id
     * @param AmLocation Id
     * @return [user.id]|false
     * 
     **/
    public function getUserByAmLocatorStore($locationId)
    {
        $sources = $this->getSourceCodeByAmLocator($locationId);
        $userTbl = $this->resource->getTableName('admin_user');
        $sourceLinkTbl = $this->resource->getTableName('admin_user_source_link');
        $users = [];
        if ($sources) {
            $sqlGetUser = $this->connection->select()
                ->from($userTbl, ['user_id'])
                ->joinLeft(['link' => $sourceLinkTbl], "link.user_id = $userTbl.user_id")
                ->where("source_code IN (?)", $sources);
            
            $users = $this->connection->fetchAll($sqlGetUser);
            $resultUserIds = [];
            foreach($users as $user) {
                if (!empty($user['user_id'])) {
                    $resultUserIds[] = $user['user_id'];
                }
            }
            return $resultUserIds;
        }
        return false;
    }
}