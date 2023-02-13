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
    const LOCATION_ASDA_LINK_TABLE = 'store_location_asda_link';
    const ADMIN_USER_SOURCE_LINK_TABLE = 'admin_user_source_link';

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
     * @return string|null
     * 
     **/

    public function getSourceCodeByAmLocator($locationId)
    {
        $sourceTbl = $this->resource->getTableName(self::LOCATION_SOURCE_LINK_TABLE);
        $sqlQuery = $this->connection->select()
                ->from($sourceTbl, ['source_code'])
                ->where("location_id = ?", $locationId);
        $result = $this->connection->fetchOne($sqlQuery);
        return $result;
    }

    /**
     * Get all source code by user or user id
     * @param $user Magento\User\Model\User or user Id
     * @return [source_code]
     * 
     **/
    public function getSourcesByUser($user)
    { 
        $userId = (gettype($user) == 'object') ? $user->getId() : $user;
        $result = [];
        if (!empty($userId)) {
            $sourcesCol = $this->sourceLink->create()->addFieldToFilter('user_id', $userId);
            
            foreach($sourcesCol as $sourceUser) {
                $result[] = $sourceUser->getData('source_code');
            }
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
        $sourcesCol = $this->sourceLink->create()->addFieldToFilter('source_code', $sourceCode);
        $result = [];
        foreach($sourcesCol as $sourceUser) {
            $result[] = $sourceUser->getData('user_id');
        }
        return $result ? : false;
    }

    /**
     * Get all store locations by User or User Id
     * @param $user Magento\User\Model\User or UserId
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
                ->where("link.source_code = ?", $sources);
            
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
        $this->connection->delete($sourceLinkTbl, 'location_id = '.$locationId);
        $this->connection->insert($sourceLinkTbl, 
            ['location_id' => $locationId, 'source_code' => $sourceCode]
        );
    }

    /**
     * Reassign all Amasty Store Location To Source tbl
     * @param AmLocation Id, Source Code
     * @return 
     * 
     **/
    public function reAssignAmLocatorStoresToSource($locationIds, $sourceCode)
    {
        $sourceLinkTbl = $this->resource->getTableName(self::LOCATION_SOURCE_LINK_TABLE);
        $this->connection->delete($sourceLinkTbl, "source_code = '$sourceCode'");
        $this->connection->delete($sourceLinkTbl, ['location_id IN (?)' => $locationIds]);
        $data = [];
        foreach ($locationIds as $locationId) {
            $data[] = ['location_id' => $locationId, 'source_code' => $sourceCode];
        }

        if ($sourceLinkTbl) {
            $this->connection->insertMultiple($sourceLinkTbl, $data);
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
    public function checkProductAvailableInStore($locationId, $product)
    {
        
        if ($locationId) {
            $sources = $this->getSourceCodeByAmLocator($locationId);

            if (empty($sources)) {
                return false;
            }

            $itemCheck = false;
            if($product->getTypeId() === "configurable") {
                $childProducts = $product->getTypeInstance()->getUsedProducts($product);
                foreach ($childProducts as $child){
                    $itemCheck = $this->checkProductItemAvailableInStore($child->getSku(), $sources);
                    if($itemCheck) {
                        break;
                    }
                }
            } else {
                $itemCheck = $this->checkProductItemAvailableInStore($product->getSku(), $sources);
            }
            
            return $itemCheck;
        }
        return true;
    }


    /**
     * check product available in current store location
     *
     * @return bool
     */
    public function checkProductItemAvailableInStore($productSku, $sources) {
        $productQty = $this->productSourceAvailability->getQuantityInformationForProduct($productSku);
            
        $sourceList = [];
        foreach ($productQty as $pQty) {
            if ($pQty['source_code'] == $sources) {
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

        return true;
    }

    /**
     * Get get Child Asda Location Collection of a Parent Location
     * @param $sourceCode string
     * @return array|null
     * 
     **/
    public function getChildAsdaLocationCollection($parentLocationId)
    {
        $sourceTbl = $this->resource->getTableName(self::LOCATION_ASDA_LINK_TABLE);
        $sqlQuery = $this->connection->select()
                ->from($sourceTbl, ['asda_location_id'])
                ->where("parent_location_id = ?", $parentLocationId);
        return $this->connection->fetchCol($sqlQuery);
    }    

    /**
     * Get Parent Location of a Asda Location
     * @param $sourceCode string
     * @return array|null
     * 
     **/
    public function getAsdaLocationParentLocation($asdaLocationId)
    {
        $asdaLinkTbl = $this->resource->getTableName(self::LOCATION_ASDA_LINK_TABLE);
        $sqlQuery = $this->connection->select()
                ->from($asdaLinkTbl, ['parent_location_id'])
                ->where("asda_location_id = ?", $asdaLocationId);
        return $this->connection->fetchOne($sqlQuery);
    }
    /**
     * Assign Amasty Parent Store Location and Asda
     * @param AmLocation Id, Source Code
     * @return 
     * 
     **/
    public function assignAsdaAmLocatorStoreToParent($parentLocationId, $asdaLocationId)
    {
        $asdaLinkTbl = $this->resource->getTableName(self::LOCATION_ASDA_LINK_TABLE);
        $data = [['parent_location_id' => $parentLocationId, 'asda_location_id' => $asdaLocationId]];
        $this->connection->insertOnDuplicate($asdaLinkTbl, $data);
    }

    /**
     * Unassign Amasty Parent Store Location and Asda
     * @param AmLocation Id, Source Code
     * @return 
     * 
     **/
    public function unAssignAsdaAmLocatorStoreToParent($parentLocationId, $asdaLocationId)
    {
        $asdaLinkTbl = $this->resource->getTableName(self::LOCATION_ASDA_LINK_TABLE);
        $data = [
                    $this->connection->quoteInto('parent_location_id = ?', $parentLocationId),
                    $this->connection->quoteInto('asda_location_id = ?', $asdaLocationId)
                ];
        if ($asdaLinkTbl) {
            $this->connection->delete($asdaLinkTbl, $data);
        }
    }

    /**
     * Assign user to source
     * @return 
     * 
     **/
    public function assignUserToSource($userId, $sourceCode)
    {
        $adminSourceTbl = $this->resource->getTableName(self::ADMIN_USER_SOURCE_LINK_TABLE);
        $data = [
            ['user_id' => $userId,'source_code' => $sourceCode]
        ];
        $this->connection->insertOnDuplicate($adminSourceTbl, $data);
    }

}