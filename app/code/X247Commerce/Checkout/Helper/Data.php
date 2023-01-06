<?php

namespace X247Commerce\Checkout\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\InventoryApi\Model\IsProductAssignedToStockInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\Data\StockSourceLinkInterface;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\InventoryConfigurableProductAdminUi\Model\GetQuantityInformationPerSource;
use Amasty\Storelocator\Model\ResourceModel\Location\CollectionFactory as LocationCollectionFactory;

class Data extends AbstractHelper
{
    protected $resource;
    protected $connection;
    protected $stockItemRepository;
    protected $getQuantityInformationPerSource;
    protected $locationCollectionFactory;

    public function __construct(
        Context $context,
        ResourceConnection $resource,
        StockItemRepository $stockItemRepository,
        GetQuantityInformationPerSource $getQuantityInformationPerSource,
        LocationCollectionFactory $locationCollectionFactory
    ) 
    {
        parent::__construct($context);
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->stockItemRepository = $stockItemRepository;
        $this->getQuantityInformationPerSource = $getQuantityInformationPerSource;
        $this->locationCollectionFactory = $locationCollectionFactory;
    }

    public function getAvailableSourceOfProduct($stockId, $productSku)
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()
            ->from(
                ['stock_source_link' => $this->resource->getTableName('inventory_source_stock_link')]
            )->join(
                ['inventory_source_item' => $this->resource->getTableName('inventory_source_item')],
                'inventory_source_item.' . SourceItemInterface::SOURCE_CODE . '
                = stock_source_link.' . SourceItemInterface::SOURCE_CODE,
                []
            )->where(
                'stock_source_link.' . StockSourceLinkInterface::STOCK_ID . ' = ?',
                $stockId
            )->where(
                'inventory_source_item.' . SourceItemInterface::SKU . ' = ?',
                $productSku
            );

        return $connection->fetchAll($select);
    }
        
    public function getStockItem($productId)
    {
        return $this->stockItemRepository->get($productId);
    }
    /**
     * @param $productSku
     * @return array
     */
    public function getQuantityInformationForProduct($productSku)
    {
        return $this->getQuantityInformationPerSource->execute($productSku);
    }
    public function getAmLocationByLocationId($id)
    {
        if (is_array($id)) {            
            return $this->locationCollectionFactory->create()->addFieldToSelect('*')
                    ->addFieldToFilter('id', array('in' => $id));
        } else {
            return $this->locationCollectionFactory->create()->addFieldToSelect('*')
                        ->addFieldToFilter('id', $id);
        }
    }
}