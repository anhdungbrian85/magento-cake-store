<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace X247Commerce\Catalog\Observer;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\ResourceConnection;

/**
 * Employ additional authorization logic when a category is saved.
 */
class CategoryPrepareSave implements ObserverInterface
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
     * @inheritDoc
     *
     * @throws AuthorizationException
     */
    public function execute(Observer $observer)
    {
        /** @var CategoryInterface $category */
        $category = $observer->getEvent()->getData('category');
        $request = $observer->getEvent()->getData('request');
        $categoryPostData = $request->getPostValue();
        if (isset($categoryPostData['category_products_popup'])
            && is_string($categoryPostData['category_products_popup'])
            && !$category->getProductsReadonly()
        ) {
            $products = json_decode($categoryPostData['category_products_popup'], true);
            $category->setPostedProductsPopup($products);
        }

        $this->_saveCategoryProductsPopup($category);
    }

    /**
     * Save category products relation
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _saveCategoryProductsPopup($category)
    {
        $category->setIsChangedProductList(false);
        $id = $category->getId();
        $connection = $this->connection;
        $resource = $this->resource;
      
        $products = $category->getPostedProductsPopup();
        $categoryProductPopupTable = $resource->getTableName('catalog_category_product_popup');
        /**
         * Example re-save category
         */
        if ($products === null) {
            return $this;
        }

        /**
         * old category-product relationships
         */
        $oldProducts = $category->getProductsPopupPosition();

        $insert = array_diff_key($products, $oldProducts);
        $delete = array_diff_key($oldProducts, $products);

        /**
         * Find product ids which are presented in both arrays
         * and saved before (check $oldProducts array)
         */
        $update = array_intersect_key($products, $oldProducts);
        $update = array_diff_assoc($update, $oldProducts);

       

        /**
         * Delete products from category
         */
        if (!empty($delete)) {
            $cond = ['product_id IN(?)' => array_keys($delete), 'category_id=?' => $id];
            $connection->delete($categoryProductPopupTable, $cond);
        }

        /**
         * Add products to category
         */
        if (!empty($insert)) {
            $data = [];
            foreach ($insert as $productId => $position) {
                $data[] = [
                    'category_id' => (int)$id,
                    'product_id' => (int)$productId,
                    'position' => (int)$position,
                ];
            }
            $connection->insertMultiple($categoryProductPopupTable, $data);
        }

        /**
         * Update product positions in category
         */
        if (!empty($update)) {
            $newPositions = [];
            foreach ($update as $productId => $position) {
                $delta = $position - $oldProducts[$productId];
                if (!isset($newPositions[$delta])) {
                    $newPositions[$delta] = [];
                }
                $newPositions[$delta][] = $productId;
            }

            foreach ($newPositions as $delta => $productIds) {
                $bind = ['position' => new \Zend_Db_Expr("position + ({$delta})")];
                $where = ['category_id = ?' => (int)$id, 'product_id IN (?)' => $productIds];
                $connection->update($categoryProductPopupTable, $bind, $where);
            }
        }

        if (!empty($insert) || !empty($delete)) {
            $productIds = array_unique(array_merge(array_keys($insert), array_keys($delete)));

            $category->setChangedProductIds($productIds);
        }

        if (!empty($insert) || !empty($update) || !empty($delete)) {
            $category->setIsChangedProductList(true);

            /**
             * Setting affected products to category for third party engine index refresh
             */
            $productIds = array_keys($insert + $delete + $update);
            $category->setAffectedProductIds($productIds);
        }
        return $this;
    }
}
