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
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use X247Commerce\Catalog\Model\ProductSourceAvailability;

class LocatorSourceResolver
{
    const LOCATION_SOURCE_LINK_TABLE = 'amasty_amlocator_location_source_link';
    protected ResourceConnection $resource;
    protected $connection;
    protected AdminSourceCollectionFactory $sourceLink;
    protected SearchCriteriaBuilder $searchCriteriaBuilder;
    protected SourceRepositoryInterface $sourceRepository;
    protected $customerSession;
    protected $searchCriteriaBuilderFactory;
    protected $productSourceAvailability;

    public function __construct(
        ResourceConnection $resource,
        AdminSourceCollectionFactory $sourceLink,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SourceRepositoryInterface $sourceRepository,
        CustomerSession $customerSession,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        ProductSourceAvailability $productSourceAvailability,
    )
    {        
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->sourceLink = $sourceLink;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sourceRepository = $sourceRepository;
        $this->customerSession = $customerSession;
        $this->productSourceAvailability = $productSourceAvailability;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
    }

    /**
     * Get amasty_amlocator_location.id
     * @param $sourceCode string
     * @return array|null
     * 
     **/

    // public function getAmLocatorBySource($sourceCode)
    // {
    //     $sourceTbl = $this->resource->getTableName('inventory_source');
    //     $sqlQuery = $this->connection->select()
    //             ->from($sourceTbl, ['amlocator_store'])
    //             ->where("source_code = ?", $sourceCode);
    //     return $this->connection->fetchOne($sqlQuery);
    // }
    public function getAmLocatorBySource($sourceCode)
    {
        $sourceTbl = $this->resource->getTableName(self::LOCATION_SOURCE_LINK_TABLE);
        $sqlQuery = $this->connection->select()
                ->from($sourceTbl, ['location_id'])
                ->where("source_code = ?", $sourceCode);
        return $this->connection->fetchCol($sqlQuery);
    }

    /**
     * Get source_code by amlocator_store id
     * @param $locationId
     * @return [source_code]|null
     * 
     **/
    // public function getSourceCodeByAmLocator($locationId)
    // {
    //     $sourceTbl = $this->resource->getTableName('inventory_source');
    //     $sqlQuery = $this->connection->select()
    //             ->from($sourceTbl, ['source_code'])
    //             ->where("amlocator_store = ?", $locationId);
    //     $results = $this->connection->fetchAll($sqlQuery);
    //     $resultSource = [];
    //     foreach($results as $result) {
    //         if (!empty($result['source_code'])) {
    //             $resultSource[] = $result['source_code'];
    //         }
    //     }
    //     return $resultSource;
    // }
    public function getSourceCodeByAmLocator($locationId)
    {
        $sourceTbl = $this->resource->getTableName(self::LOCATION_SOURCE_LINK_TABLE);
        $sqlQuery = $this->connection->select()
                ->from($sourceTbl, ['source_code'])
                ->where("location_id = ?", $locationId);
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
        if ($user) {
            $userId = $user->getId();
            if ($userId) {
                $sourcesCol = $this->sourceLink->create()->addFieldToFilter('user_id', $userId);
                $result = [];
                foreach($sourcesCol as $sourceUser) {
                    $result[] = $sourceUser->getData('source_code');
                }
                return $result ? : false;
            }
        }
    }

    /**
     * Get all users by source code
     * @param sourceCode string
     * @return [Magento\User\Model\User]
     * 
     **/
    public function getUserBySource($sourceCode)
    {
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
        $sourceTbl = $this->resource->getTableName(self::LOCATION_SOURCE_LINK_TABLE);

        if ($sources) {
            $sqlGetSources = $this->connection->select()
                ->from($sourceTbl, ['location_id'])
                ->where("source_code IN (?)", $sources);
            
            $amStores = $this->connection->fetchAll($sqlGetSources);
            $resultAmStoreIds = [];
            foreach($amStores as $amStore) {
                if (!empty($amStore['location_id'])) {
                    $resultAmStoreIds[] = $amStore['location_id'];
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
    /**
     * Assign Amasty Store Location To Source
     * @param AmLocation Id, Source Code
     * @return 
     * 
     **/
    public function assignAmLocatorStoreToSource($locationId, $sourceCode)
    {
        $sourceLinkTbl = $this->resource->getTableName(self::LOCATION_SOURCE_LINK_TABLE);
        $data =['location_id' => $locationId, 'source_code' => $sourceCode];
        if ($sourceLinkTbl) {
            $this->connection->insert($sourceLinkTbl, $data);
        }
    }
    /**
     * Unassign Amasty Store Location To Source
     * @param AmLocation Id, Source Code
     * @return 
     * 
     **/
    public function unAssignAmLocatorStoreWithSource($locationId, $sourceCode)
    {
        $sourceLinkTbl = $this->resource->getTableName(self::LOCATION_SOURCE_LINK_TABLE);
        $data = [
                    $this->connection->quoteInto('location_id = ?', $locationId),
                    $this->connection->quoteInto('source_code = ?', $sourceCode)
                ];
        if ($sourceLinkTbl) {
            $this->connection->delete($sourceLinkTbl, $data);
        }
    }
    /**
     * check product available in current store location
     *
     * @return bool
     */
    public function checkProductAvailableInStore($locationId, $productSku)
    {
        
        if ($locationId) {
            $sources = $this->getSourceCodeByAmLocator($locationId);

            if ($sources) {
                $sourceCodes = [];
                foreach ($sources as $source) {
                    $sourceCodes[] =  $source;
                }
            } else {
                return false;
            }
            
            $productQty = $this->productSourceAvailability->getQuantityInformationForProduct($productSku);
            
            $sourceList = [];
            foreach ($productQty as $pQty) {
                if (in_array($pQty['source_code'], $sourceCodes)) {
                    $sourceList[] = $pQty;
                }
            }        

            if ($sourceList) {
                $inStock = 0;
                foreach ($sourceList as $qty) {
                    if ($qty['quantity'] == 0 || !$qty['status']) {
                        $inStock += 0;
                    } else {
                        $inStock += 1;
                    }
                }

                if ($inStock == 0) {
                    return false;
                }
            } else {
                return false;       
            }
        }
        return true;
    }
}