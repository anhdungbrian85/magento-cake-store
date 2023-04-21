<?php

namespace X247Commerce\Products\Observer\Product;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface;
use Magento\InventoryCatalogApi\Model\GetProductTypesBySkusInterface;
use Magento\Framework\DB\Adapter\DuplicateException;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\Inventory\Model\ResourceModel\SourceItem;

class UpdateSoureProductAfterSave implements ObserverInterface
{
    protected $resourceConnection;

    protected $isSourceItemManagementAllowedForProductType;

    protected $searchCriteriaBuilder;

    protected $sourceRepository;

    protected $getProductTypesBySkus;

    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SourceRepositoryInterface $sourceRepository,
        ResourceConnection $resourceConnection,
        GetProductTypesBySkusInterface $getProductTypesBySkus,
        IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sourceRepository = $sourceRepository;
        $this->resourceConnection = $resourceConnection;
        $this->isSourceItemManagementAllowedForProductType = $isSourceItemManagementAllowedForProductType;
        $this->getProductTypesBySkus = $getProductTypesBySkus;
    }

    public function execute(EventObserver $observer)
    {
        $product = $observer->getData('product');
        $sourceList = $this->getSourcesList();
        if (!empty($sourceList)) {
            if($product->getTypeId() === "configurable") {
                $skus = [];
                $childProducts = $product->getTypeInstance()->getUsedProducts($product);
                foreach ($childProducts as $child){
                    $skus[] = $child->getSku();
                }
                $this->saveSourceItem($skus, $sourceList);
            } else {
                $this->saveSourceItem([$product->getSku()], $sourceList);
            }
        }

        return;
    }

    public function getSourcesList()
    {
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $sourceList = [];
        try {
            $sourceData = $this->sourceRepository->getList($searchCriteria);
            if ($sourceData->getTotalCount()) {
                foreach ($sourceData->getItems() as $source) {
                    $sourceList[] = $source->getData('source_code');
                }
            }
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
        return $sourceList;
    }

    public function saveSourceItem(array $skus, array $sourceCodes)
    {
        $types = $this->getProductTypesBySkus->execute($skus);
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName(SourceItem::TABLE_NAME_SOURCE_ITEM);

        foreach ($types as $sku => $type) {
            if ($this->isSourceItemManagementAllowedForProductType->execute($type)) {
                foreach ($sourceCodes as $sourceCode) {
                    try {
                        $connection->insert($tableName, [
                            SourceItemInterface::SOURCE_CODE => $sourceCode,
                            SourceItemInterface::SKU => $sku,
                            SourceItemInterface::QUANTITY => 100,
                            SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
                        ]);
                    } catch (DuplicateException $e) {
                        continue;
                    }
                }

                // die('2121');
            }
        }
    }
}
