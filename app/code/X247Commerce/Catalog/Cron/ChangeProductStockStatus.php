<?php

namespace X247Commerce\Catalog\Cron;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\InventoryIndexer\Indexer\InventoryIndexer;
use Magento\Framework\Indexer\IndexerRegistry;
use Psr\Log\LoggerInterface;
class ChangeProductStockStatus
{
    protected ProductCollectionFactory $productCollectionFactory;
    protected StockItemInterface $stockItem;
    protected InventoryIndexer $inventoryIndexer;
    protected ResourceConnection $resource;
    protected IndexerRegistry $indexerRegistry;
    protected $connection;
    protected LoggerInterface $logger;

    public function __construct(
        ProductCollectionFactory $productCollectionFactory,
        StockItemInterface $stockItem,
        InventoryIndexer $inventoryIndexer,
        ResourceConnection $resource,
        IndexerRegistry $indexerRegistry,
        LoggerInterface $logger
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->stockItem = $stockItem;
        $this->inventoryIndexer = $inventoryIndexer;
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->indexerRegistry = $indexerRegistry;
        $this->logger = $logger;
    }


    public function execute()
    {
        $productCollectionFactory = $this->productCollectionFactory;
        $inventorySourceTbl = $this->resource->getTableName('inventory_source_item');

        $productCollection = $productCollectionFactory
            ->create()
            ->addAttributeToSelect('*')
            ->addAttributeToSort('created_at', 'DESC')
            ->addFieldToFilter('type_id', 'configurable')
            ->joinField('stock_item', 'cataloginventory_stock_item', 'is_in_stock', 'product_id=entity_id', 'is_in_stock=0');

        $indexLists = ['cataloginventory_stock', 'inventory'];

        if (count($productCollection)) {
            $skus = [];
            $productIds = [];
            foreach ($productCollection as $product) {

                try {
                    $productId = $product->getId();
                    $productIds[] = $productId;
                    $skus[] = $product->getSku();
                    $stockItem =    $this->stockItem->load($productId, 'product_id');
                    $stockItem->setData('is_in_stock', 1);
                    $stockItem->setData('qty', 100);
                    $stockItem->save();
                    $product->setData('quantity_and_stock_status' , ['is_in_stock' => 1]);
                    $product->setData('stock_data', ['is_in_stock' => 1]);
                    $product->save();


                }   catch (\Exception $exception) {
                    $this->logger->info("Cannot change stock status: ". $exception->getMessage());
                }
            }

            foreach ($indexLists as $indexList) {
                try {
                    $indexer = $this->indexerRegistry->get($indexList);
                    $indexer->reindexList(array_unique($productIds));
                } catch (\Exception $exception) {
                    $this->logger->info("Cannot reindex: $indexList - ". $exception->getMessage());
                }
            }

//            $select = $this->connection->select()
//                    ->from(['i' => $inventorySourceTbl], 'source_item_id')
//                    ->where('sku in (?)', $skus);
//
//            $sourceItemIds = $this->connection->fetchCol($select);
//            $this->inventoryIndexer->executeList($sourceItemIds);
        }
    }
}

