<?php

declare(strict_types=1);

namespace X247Commerce\Catalog\Model\ResourceModel;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Indexer\Category\Product\Processor;
use Magento\Catalog\Setup\CategorySetup;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\MetadataPool;

class Category extends \Magento\Catalog\Model\ResourceModel\Category
{
    /**
     * Catalog products table name
     *
     * @var string
     */
    protected $_categoryProductPopupTable;
    
    /**
     * @var array[]
     */
    private $entitiesWhereAttributesIs;

    /**
     * @var Processor
     */
    private $indexerProcessor;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    public function __construct(
        \Magento\Eav\Model\Entity\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Factory $modelFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Catalog\Model\ResourceModel\Category\TreeFactory $categoryTreeFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        Processor $indexerProcessor,
        $data = [],
        \Magento\Framework\Serialize\Serializer\Json $serializer = null,
        MetadataPool $metadataPool = null,
        \Magento\Framework\EntityManager\EntityManager $entityManager = null,
        \Magento\Catalog\Model\ResourceModel\Category\AggregateCount $aggregateCount = null
    ) {
        parent::__construct(
            $context,
            $storeManager,
            $modelFactory,
            $eventManager,
            $categoryTreeFactory,
            $categoryCollectionFactory,
            $indexerProcessor,
            $data,
            $serializer,
            $metadataPool,
            $entityManager,
            $aggregateCount
        );
        $this->indexerProcessor = $indexerProcessor;
        $this->metadataPool = $metadataPool ?: ObjectManager::getInstance()->get(MetadataPool::class);
    }

    /**
     * Category product table name getter
     *
     * @return string
     */
    public function getCategoryProductPopupTable()
    {
        if (!$this->_categoryProductPopupTable) {
            $this->_categoryProductPopupTable = $this->getTable('catalog_category_product_popup');
        }
        return $this->_categoryProductPopupTable;
    }

    /**
     * Process category data after save category object
     *
     * Save related products ids and update path value
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\DataObject $object)
    {
        /**
         * Add identifier for new category
         */
        if (substr((string)$object->getPath(), -1) == '/') {
            $object->setPath($object->getPath() . $object->getId());
            $this->_savePath($object);
        }

        $this->_saveCategoryProducts($object);
        $this->_saveCategoryProductsPopup($object);
        return parent::_afterSave($object);
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
        /**
         * new category-product relationships
         */
        $products = $category->getPostedProductsPopup();

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

        $connection = $this->getConnection();

        /**
         * Delete products from category
         */
        if (!empty($delete)) {
            $cond = ['product_id IN(?)' => array_keys($delete), 'category_id=?' => $id];
            $connection->delete($this->getCategoryProductPopupTable(), $cond);
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
            $connection->insertMultiple($this->getCategoryProductPopupTable(), $data);
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
                $connection->update($this->getCategoryProductPopupTable(), $bind, $where);
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
            $this->_eventManager->dispatch(
                'catalog_category_change_products',
                ['category' => $category, 'product_ids' => $productIds]
            );
            $category->setAffectedProductIds($productIds);
        }
        return $this;
    }

    /**
     * Get positions of associated to category products
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return array
     */
    public function getProductsPopupPosition($category)
    {
        $select = $this->getConnection()->select()->from(
            $this->getCategoryProductPopupTable(),
            ['product_id', 'position']
        )->where(
            "{$this->getTable('catalog_category_product_popup')}.category_id = ?",
            $category->getId()
        );
        $websiteId = $category->getStore()->getWebsiteId();
        if ($websiteId) {
            $select->join(
                ['product_website' => $this->getTable('catalog_product_website')],
                "product_website.product_id = {$this->getTable('catalog_category_product_popup')}.product_id",
                []
            )->where(
                'product_website.website_id = ?',
                $websiteId
            );
        }

        return $this->getConnection()->fetchPairs($select);
    }
}