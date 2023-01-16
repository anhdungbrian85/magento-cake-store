<?php

namespace X247Commerce\Nutritics\Cron;

use X247Commerce\Nutritics\Service\NutriticsApi;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\ResourceConnection;
use X247Commerce\Nutritics\Helper\Config as ConfigHelper;

class Nutritics
{
    const TABLE_NUTRITICS_PRODUCT_ATTRIBUTE_VALUE = 'nutritics_product_attribute_value';
	protected $nutriticsApi;
	protected $productRepository;
	protected $searchCriteriaBuilder;
	protected $productCollectionFactory;
    protected $resource;
    protected $connection;
    protected $configHelper;

    public function __construct(
    	ProductRepositoryInterface $productRepository,
    	SearchCriteriaBuilder $searchCriteriaBuilder,
    	CollectionFactory $productCollectionFactory,
        NutriticsApi $nutriticsApi,
        ResourceConnection $resource,
        ConfigHelper $configHelper
    ) {
		$this->productRepository = $productRepository;
    	$this->searchCriteriaBuilder = $searchCriteriaBuilder;
    	$this->productCollectionFactory = $productCollectionFactory;
        $this->nutriticsApi = $nutriticsApi;
        $this->resource = $resource;
        $this->configHelper = $configHelper;
        $this->connection = $resource->getConnection();
    }

    public function execute()
    {
        $productCollection = $this->getProductCollection();
        $filterAttr = $this->configHelper->getProductApiAttributeFilter();

        foreach ($productCollection as $product) {
            $nutricInfo = [];
            if ($filterAttr == ConfigHelper::NUTRITICS_CONFIG_API_ATTRIBUTE_IFC) {
                if ($product->getIfcCode()) {
                    $nutricInfo = $this->getNutriticsInfo($product->getIfcCode());
                }
            }   else {
                $nutricInfo = $this->getNutriticsInfo($product->getSku());
            }

            
            if ($nutricInfo) {
                $this->insertNutriticsInfo($product->getEntityId(), $nutricInfo);
            }            
        }
    }

    /**
     * Get Insert Product Nutritics Info to table nutritics_product_attribute_value
     * @param $product id, array $nutricInfo
     * @return 
     */
    public function insertNutriticsInfo($productId, $nutricInfo)
    {
        $table = $this->resource->getTableName(self::TABLE_NUTRITICS_PRODUCT_ATTRIBUTE_VALUE);
        if ($productId && $nutricInfo) {
            $insertData = [];
            foreach ($nutricInfo as $key => $value) {
                $nutrics = [];
                $allergens = [];
                if ($key != 'id') {
                    if (isset($value['name'])) {
                        if ($value['val']) {
                            $nutrics = ['row_id' => $productId, 'attribute_code' => $key, 'attribute_name' => $value['name'], 'value' => $value['val'], 'attribute_unit' => $value['unit'], 
                                        'percent_ri' => $value['percentRI']];
                        }
                        
                    }
                    if (isset($value['contains'])) {
                        $allergens = ['row_id' => $productId, 'attribute_code' => $key, 'attribute_name' => $key, 'value' => json_encode($value), 'attribute_unit' => '', 
                        'percent_ri' => ''];
                    }
                    if (!empty($nutrics) || !empty($allergens)) {
                        $insertData[] = array_merge($nutrics, $allergens);
                    }                    
                }
                
            }
            if ($insertData) {
                return $this->connection->insertMultiple($table, $insertData);
            }
            return;
        }
    }

    public function getNutriticsInfo($ifc) 
    {
    	$getNutriticsEnergy = $this->getNutriticsEnergy($ifc);
        $getNutriticsMacro = $this->getNutriticsMacro($ifc);
        $getNutriticsCarbohydrates = $this->getNutriticsCarbohydrates($ifc);
        $getNutriticsFats = $this->getNutriticsFats($ifc);
        $getNutriticsMinerals = $this->getNutriticsMinerals($ifc);
        $getNutriticsVitamins = $this->getNutriticsVitamins($ifc);
        $getNutriticsOther = $this->getNutriticsOther($ifc);
        $getNutriticsMiscellaneous = $this->getNutriticsMiscellaneous($ifc);

        $allInfo = array_merge($getNutriticsEnergy, $getNutriticsMacro, $getNutriticsCarbohydrates, $getNutriticsFats, $getNutriticsMinerals, 
                                $getNutriticsVitamins, $getNutriticsFats, $getNutriticsMinerals, $getNutriticsVitamins, $getNutriticsOther, $getNutriticsMiscellaneous);

        return $allInfo;
    }
    
    public function getNutriticsReportUrl($ifc) 
    {
        $nutriticsInfo = json_decode($this->nutriticsApi->getFoodProductByIfc($ifc), true);
        $reporturl = $nutriticsInfo[1]['reporturl'];
        return $reporturl;
    }

    /**
     * Get Energy info of Product by product's ifc code
     * @param 
     * @return array
     */
    public function getNutriticsEnergy($ifc) 
    {
        $nutriticsInfo = json_decode($this->nutriticsApi->getFoodProductByIfc($ifc, ['energyKcal', 'energyKj']), true);
        
        $return  = isset($nutriticsInfo[1]) ? $nutriticsInfo[1] : [] ;
        return $return;
    }
    /**
     * Get Macronutrients info of Product by product's ifc code
     * @param 
     * @return array
     */
    public function getNutriticsMacro($ifc) 
    {
        $nutriticsInfo = json_decode($this->nutriticsApi->getFoodProductByIfc($ifc, ['carbohydrate', 'protein', 'fat', 'water', 'waterDr', 'alcohol']), true);
        $return  = isset($nutriticsInfo[1]) ? $nutriticsInfo[1] : [] ;
        return $return;
    }
    /**
     * Get Carbohydrates info of Product by product's ifc code
     * @param 
     * @return array
     */
    public function getNutriticsCarbohydrates($ifc) 
    {
        $nutriticsInfo = json_decode($this->nutriticsApi->getFoodProductByIfc($ifc, ['starch','oligosaccharide','fibre','nsp','sugars','freesugars','glucose','galactose','fructose','sucrose','maltose','lactose']), true);
        $return  = isset($nutriticsInfo[1]) ? $nutriticsInfo[1] : [] ;
        return $return;
    }
    /**
     * Get Fats (Lipid Components) info of Product by product's ifc code
     * @param 
     * @return array
     */
    public function getNutriticsFats($ifc) 
    {
        $nutriticsInfo = json_decode($this->nutriticsApi->getFoodProductByIfc($ifc, ['satfat','monos','cismonos','poly','n3poly','n6poly','cispoly','trans','cholesterol']), true);
        $return  = isset($nutriticsInfo[1]) ? $nutriticsInfo[1] : [] ;
        return $return;
    }
    /**
     * Get Minerals and Trace Elements info of Product by product's ifc code
     * @param 
     * @return array
     */
    public function getNutriticsMinerals($ifc) 
    {
        $nutriticsInfo = json_decode($this->nutriticsApi->getFoodProductByIfc($ifc, ['sodium','potassium','chloride','calcium','phosphorus','magnesium','iron','zinc','copper','manganese','selenium','iodine']), true);
        $return  = isset($nutriticsInfo[1]) ? $nutriticsInfo[1] : [] ;
        return $return;
    }
    /**
     * Get Vitamins info of Product by product's ifc code
     * @param 
     * @return array
     */
    public function getNutriticsVitamins($ifc) 
    {
        $nutriticsInfo1 = json_decode($this->nutriticsApi->getFoodProductByIfc($ifc, ['vita','retinol','carotene','vitd','vite','vitk','thiamin','riboflavin','niacineqv']), true);
        $nutriticsInfo2 = json_decode($this->nutriticsApi->getFoodProductByIfc($ifc, ['niacin','tryptophan','pantothenate','vitb6','folate','vitb12','biotin','vitc']), true);
        $return1  = isset($nutriticsInfo[1]) ? $nutriticsInfo[1] : [] ;
        $return2  = isset($nutriticsInfo[2]) ? $nutriticsInfo[2] : [] ;
        return array_merge($return1, $return2);
    }
    /**
     * Get Other info of Product by product's ifc code
     * @param 
     * @return array
     */
    public function getNutriticsOther($ifc) 
    {
        $nutriticsInfo = json_decode($this->nutriticsApi->getFoodProductByIfc($ifc, ['gi','gl','caffeine']), true);
        $return  = isset($nutriticsInfo[1]) ? $nutriticsInfo[1] : [] ;
        return $return;
    }
    /**
     * Get Miscellaneous info of Product by product's ifc code
     * @param 
     * @return array
     */
    public function getNutriticsMiscellaneous($ifc) 
    {
        $nutriticsInfo = json_decode($this->nutriticsApi->getFoodProductByIfc($ifc, ['allergens']), true);
        $return  = isset($nutriticsInfo[1]) ? $nutriticsInfo[1] : [] ;
        return $return;
    }

    /**
     * Get Product Collection not set value in nutritics_product_attribute_value
     * @param 
     * @return Magento\Catalog\Model\ResourceModel\Product\Collection
     */
	public function getProductCollection()
    {
        $table = $this->resource->getTableName(self::TABLE_NUTRITICS_PRODUCT_ATTRIBUTE_VALUE);
        //Query to get all product id in table nutritics_product_attribute_value
        $productQuery = $this->connection->select()->from(['nut_tbl' => self::TABLE_NUTRITICS_PRODUCT_ATTRIBUTE_VALUE],['nut_tbl.row_id'])->group('nut_tbl.row_id');
        $productIds = $this->connection->fetchCol($productQuery);
        
		$collection = $this->productCollectionFactory->create()->addAttributeToSelect('ifc_code');

        if ($productIds) {
            $collection->addFieldToFilter('entity_id', ['nin'=>$productIds]);
        }
        return $collection;
	}
}