<?php
namespace X247Commerce\PopupAddtoCart\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class UpdateInputCategoryPopupProductAttribute implements DataPatchInterface
{
    public $valueDefault;
    public $connection;
    public $resource;
    public $categoryCollectionFactory;
    /** @var ModuleDataSetupInterface */
    private $moduleDataSetup;

    /** @var EavSetupFactory */
    private $eavSetupFactory;
    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        \X247Commerce\Catalog\Setup\Patch\Data\SetDefaultValueCategoryPopup $valueDefault,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        ResourceConnection $resource
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->valueDefault = $valueDefault;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->connection = $resource->getConnection();
        $this->resource = $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $defaultCategoryValue = $this->getDefaultValueCategory();
        
        if ($defaultCategoryValue != '') {
            $tableName = $this->resource->getTableName('catalog_product_entity_text');
            $attributeId = $this->valueDefault->getAttributeId();
            $products = $this->valueDefault->getProductCollection();
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
                $eavSetup->updateAttribute(\Magento\Catalog\Model\Product::ENTITY, 
                    'category_show_in_popup_crossell', 
                    [
                        'frontend_input' => 'text',
                        'default_value' => $defaultCategoryValue
                    ]);

	        	$this->valueDefault->clearOldData($this->valueDefault->resource->getTableName('catalog_product_entity_text'), $this->valueDefault->getAttributeId());
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
        return [
            \X247Commerce\Catalog\Setup\Patch\Data\SetDefaultValueCategoryPopup::class
        ];
    }

    public function getDefaultValueCategory()
    {
        $categoryCollection = $this->categoryCollectionFactory->create();
        $categories = $categoryCollection->addAttributeToFilter('name', ['in', ['Candles','Balloons']]);
        $valueDefault = [];
        $i = 0;
        foreach ($categories as $item) {
            $valueDefault['custom_field'][] = [
                'record_id' => (string)$i,
                'select_field' => $item->getId(),
                'initialize' => true
            ];
            $i++;
        }

        return count($valueDefault) > 0 ? json_encode($valueDefault) : '';
    }
}