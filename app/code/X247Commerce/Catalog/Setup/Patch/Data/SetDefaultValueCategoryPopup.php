<?php

declare (strict_types = 1);

namespace X247Commerce\Catalog\Setup\Patch\Data;

use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\App\ResourceConnection;

class SetDefaultValueCategoryPopup implements DataPatchInterface, PatchRevertableInterface
{
	const ERROR_CODE_DUPLICATE_ENTRY = 23000;

    /**
     * ModuleDataSetupInterface
     *
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * EavSetupFactory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * CollectionFactory
     *
     * @var categoryCollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory          $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory, 
        AttributeRepositoryInterface $attributeRepository,
        ResourceConnection $resource
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->attributeRepository = $attributeRepository;
        $this->connection = $resource->getConnection();
        $this->resource = $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function apply() 
    {
    	$tableName = $this->resource->getTableName('catalog_product_entity_text');
        $products = $this->getProductCollection();
        $defaultCategoryValue = $this->getDefaultCategory();

        if ($defaultCategoryValue != '') {
	        $attributeId = $this->getAttributeId();
	        $data = [];

	        foreach ( $products as $product) {
	        	$data[] = [
	        		'attribute_id' => $attributeId,
	        		'store_id' => '0',
	        		'value' => $defaultCategoryValue,
	        		'row_id' => $product->getRowId()
	        	];
	        }

	        try {
	        	$this->clearOldData($tableName, $attributeId);
	            return $this->connection->insertMultiple($tableName, $data);
	        } catch (\Exception $e) {
	            if ($e->getCode() === self::ERROR_CODE_DUPLICATE_ENTRY
	                && preg_match('#SQLSTATE\[23000\]: [^:]+: 1062[^\d]#', $e->getMessage())
	            ) {
	                throw new \Magento\Framework\Exception\AlreadyExistsException(
	                    __('URL key for specified store already exists.')
	                );
	            }
	            throw $e;
	        }
        }
    }

    public function revert()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
        	\X247Commerce\Catalog\Setup\Patch\Data\AddCategoryPopupProductAttribute::class
        ];
    }

    public function getDefaultCategory()
    {
        $categoryCollection = $this->categoryCollectionFactory->create();
        $categories = $categoryCollection->addAttributeToFilter('name', ['in', ['Candles','Balloons']]);
        $categoryIds = '';

		if (count($categories) > 0) {
			foreach ($categories as $item) {
				$categoryIds .= $item->getId() . ",";
			}

			$categoryIds = rtrim($categoryIds, ",");
            return $categoryIds;
		}

        return '';
    }

    public function getAttributeId()
    {
        $attribute = $this->attributeRepository->get(Product::ENTITY, 'category_show_in_popup_crossell');
        return $attribute->getAttributeId();
    }

    public function getProductCollection()
    {
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        
        return $collection;
    }

    public function clearOldData($tableName, $attributeId)
    {
    	$connection = $this->connection;

    	$connection->beginTransaction();
        try {
            $connection->delete($tableName, 'attribute_id = '. $attributeId);
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
    }
}