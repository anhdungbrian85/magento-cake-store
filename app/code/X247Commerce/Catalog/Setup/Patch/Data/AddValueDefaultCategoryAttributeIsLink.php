<?php

namespace X247Commerce\Catalog\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use \Magento\Framework\App\ResourceConnection;

class AddValueDefaultCategoryAttributeIsLink implements DataPatchInterface
{
    protected $resource;

    protected $categoryCollectionFactory;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->resource = $resource;
    }

    public function apply()
    {
        $connection = $this->resource->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
        $tablename = $connection->getTableName('catalog_category_entity_int');

        $sql = "DELETE  FROM $tablename WHERE (attribute_id = 217 AND store_id = 0)";     
        $connection->query($sql);

        $categories = $this->categoryCollectionFactory->create();
        $categories->addAttributeToSelect('*')->addIsActiveFilter();
        $dataInsert = [];
        foreach ($categories as $category) {
            $dataInsert[] = [
                'attribute_id' => 217,
                'store_id' => 0,
                'value' => 1,
                'row_id' => $category->getRowId()
            ];
        }
        $connection->insertMultiple($tablename, $dataInsert);
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
