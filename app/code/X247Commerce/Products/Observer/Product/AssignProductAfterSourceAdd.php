<?php

namespace X247Commerce\Products\Observer\Product;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface;
use Magento\InventoryCatalogApi\Model\GetProductTypesBySkusInterface;
use Magento\Framework\DB\Adapter\DuplicateException;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\Inventory\Model\ResourceModel\SourceItem;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;

class AssignProductAfterSourceAdd implements ObserverInterface
{
    protected $resourceConnection;

    protected $isSourceItemManagementAllowedForProductType;

    protected $getProductTypesBySkus;

    protected $productCollectionFactory;

    public function __construct(
        ResourceConnection $resourceConnection,
        GetProductTypesBySkusInterface $getProductTypesBySkus,
        IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType,
        ProductCollectionFactory $productCollectionFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->isSourceItemManagementAllowedForProductType = $isSourceItemManagementAllowedForProductType;
        $this->getProductTypesBySkus = $getProductTypesBySkus;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    public function execute(EventObserver $observer)
    {
        $object = $observer->getData('object');
        if ($object instanceof \Magento\InventoryApi\Api\Data\SourceInterface) {
            $i = 1;
            while(true) {
                $products = $this->getProductCollection($i);
                if ($object->getData() && $products->getSize() > ($i - 1)*100) {
                    foreach ($products as $product) {
                        if($product->getTypeId() === \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE) {
                            $this->saveSourceItem([$product->getSku()], $object->getData('source_code'));
                        }
                    }
                } else {
                    break;
                }

                $i++;
            }
        }
        
        

        return;
    }

    public function getProductCollection($page)
    {
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->setPageSize(100);
        $collection->setCurPage($page);
        return $collection;
    }

    public function saveSourceItem(array $skus, $sourceCode)
    {
        $types = $this->getProductTypesBySkus->execute($skus);
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName(SourceItem::TABLE_NAME_SOURCE_ITEM);

        foreach ($types as $sku => $type) {
            if ($this->isSourceItemManagementAllowedForProductType->execute($type)) {
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
        }
    }
}
