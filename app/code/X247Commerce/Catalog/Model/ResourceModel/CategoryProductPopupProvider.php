<?php

declare(strict_types=1);

namespace X247Commerce\Catalog\Model\ResourceModel;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Setup\CategorySetup;
use Magento\Framework\DataObject;
use Magento\Framework\App\ResourceConnection;

class CategoryProductPopupProvider 
{
    protected $resource;
    protected $connection;

    public function __construct(
        ResourceConnection $resource

    ) {
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
    }


    /**
     * Get positions of associated to category products
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return array
     */
    public function getProductsPopupPosition($category)
    {
        $connection = $this->connection;
        $resource = $this->resource;
        $select = $connection->select()->from(
            $resource->getTableName('catalog_category_product_popup'),
            ['product_id', 'position']
        )->where(
            "{$resource->getTableName('catalog_category_product_popup')}.category_id = ?",
            $category->getId()
        );
        $websiteId = $category->getStore()->getWebsiteId();
        if ($websiteId) {
            $select->join(
                ['product_website' => $resource->getTableName('catalog_product_website')],
                "product_website.product_id = {$resource->getTableName('catalog_category_product_popup')}.product_id",
                []
            )->where(
                'product_website.website_id = ?',
                $websiteId
            );
        }

        return $connection->fetchPairs($select);
    }
}