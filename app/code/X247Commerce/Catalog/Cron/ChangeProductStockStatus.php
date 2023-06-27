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
        $productCollection = $productCollectionFactory
            ->create()
            ->addAttributeToSelect('*')
            ->addAttributeToSort('created_at', 'DESC')
            ->addFieldToFilter('type_id', 'configurable')
            ->joinField('stock_item', 'cataloginventory_stock_item', 'is_in_stock', 'product_id=entity_id', 'is_in_stock=0');
			$this->logger->info('------------------ProductCollection---------------------');      

        if (count($productCollection)) {           
            foreach ($productCollection as $product) {
                try {
					$this->logger->info('------------------ProductSku---------------------'.$product->getSku());
                    $productId = $product->getId();
                    $productSku = $product->getSku();					
					$stockeTable = $this->connection->getTableName('inventory_stock_3');
					$this->connection->update($stockeTable, ['is_salable' => '1'], ['sku = ?' => $productSku]);					
					$inventoryTable = $this->connection->getTableName('cataloginventory_stock_item');
					$this->connection->update($inventoryTable, ['is_in_stock' => '1'], ['product_id = ?' => $productId]);					
					$product->save();                   
                }catch (\Exception $exception) {
                    $this->logger->info("Cannot change stock status: ". $exception->getMessage());
                }
            }

        }
    }
}

