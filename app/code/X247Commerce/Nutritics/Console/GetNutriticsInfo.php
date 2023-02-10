<?php
 
namespace X247Commerce\Nutritics\Console;

use \Symfony\Component\Console\Command\Command;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;
use X247Commerce\Nutritics\Service\NutriticsApi;
use X247Commerce\Nutritics\Helper\Config as ConfigHelper;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;

class GetNutriticsInfo extends Command
{
    const TABLE_NUTRITICS_PRODUCT_ATTRIBUTE_VALUE = 'nutritics_product_attribute_value';

    protected $nutriticsApi;
    protected $productRepository;
    protected $searchCriteriaBuilder;
    protected $productCollectionFactory;
    protected $resource;
    protected $connection;
    protected $configHelper;
    protected $logger;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CollectionFactory $productCollectionFactory,
        NutriticsApi $nutriticsApi,
        ResourceConnection $resource,
        LoggerInterface $logger,
        ConfigHelper $configHelper
    ) {
        parent::__construct();
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->nutriticsApi = $nutriticsApi;
        $this->resource = $resource;
        $this->configHelper = $configHelper;
        $this->logger = $logger;
        $this->connection = $resource->getConnection();
    }

    protected function configure()
    {
        $this->setName('nutritics:fetch')
             ->setDescription('Fetch Nutritics Info');

        parent::configure();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('This process might take long time, please wait!');
        $productCollection = $this->getProductCollection();
        $filterAttr = $this->configHelper->getProductApiAttributeFilter();

        $allNutricInfo = [];
        $allIfcCode = [];
        $allSkus = [];
        $allProductIds = [];
        foreach ($productCollection as $product) {
            $nutricInfo = [];
            $allProductIds[] = $product->getEntityId();
            if ($filterAttr == ConfigHelper::NUTRITICS_CONFIG_API_ATTRIBUTE_IFC) {
                if ($product->getIfcCode()) {
                    $allIfcCode[] = $product->getIfcCode();
                    $nutricInfo = $this->getNutriticsInfo($product->getIfcCode());
                }
            }   else {
                $allSkus[] = $product->getSku();
                $nutricInfo = $this->getNutriticsInfo($product->getSku());
            }

            if ($nutricInfo) {
                $this->insertNutriticsInfo($product->getEntityId(), $nutricInfo);
            }            
        }

        // if (!empty($allIfcCode)) {
        //     $allNutricInfo = $this->getNutriticsInfo($allIfcCode);
        // }
        // if (!empty($allSkus)) {
        //     $allNutricInfo = $this->getNutriticsInfo($allSkus);
        // }
        // if (!empty($allNutricInfo)) {
        //     $this->insertMultiNutriticsInfo($allProductIds, $allNutricInfo);
        // }
    }

    /**
     * Get Insert Product Nutritics Info to table nutritics_product_attribute_value
     * @param $productId, array $nutricInfo
     * @return 
     */
    public function insertNutriticsInfo($productId, $nutricInfo)
    { var_dump($productId);var_dump($nutricInfo);
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

    /**
     * Get Insert Product Nutritics Info to table nutritics_product_attribute_value
     * @param array $productId, array $nutricInfo
     * @return 
     */
    public function insertMultiNutriticsInfo($productId, $nutricInfo)
    {
        try {
            $table = $this->resource->getTableName(self::TABLE_NUTRITICS_PRODUCT_ATTRIBUTE_VALUE);
            
            for ($i=0; $i < count($productId); $i++)
            {
                $insertData = [];
                foreach ($nutricInfo as $key => $value) {
                    $nutrics = [];
                    $allergens = [];
                    if ($key != 'id') {
                        if (isset($value[$i]['name'])) {
                            if ($value[$i]['val']) {
                                $nutrics = ['row_id' => $productId[$i], 'attribute_code' => $key, 'attribute_name' => $value[$i]['name'], 'value' => $value[$i]['val'], 'attribute_unit' => $value[$i]['unit'], 
                                            'percent_ri' => $value[$i]['percentRI']];
                            }
                            
                        }
                        if (isset($value[$i + 1]['contains'])) {
                            $allergens = ['row_id' => $productId[$i], 'attribute_code' => $key, 'attribute_name' => $key, 'value' => json_encode($value), 'attribute_unit' => '', 
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
                return null;
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        
    }

    public function getNutriticsInfo($filterCode) 
    {
        $getNutriticsEnergy = $this->getNutriticsEnergy($filterCode);
        $getNutriticsMacro = $this->getNutriticsMacro($filterCode);
        $getNutriticsCarbohydrates = $this->getNutriticsCarbohydrates($filterCode);
        $getNutriticsFats = $this->getNutriticsFats($filterCode);
        $getNutriticsMinerals = $this->getNutriticsMinerals($filterCode);
        $getNutriticsVitamins = $this->getNutriticsVitamins($filterCode);
        $getNutriticsOther = $this->getNutriticsOther($filterCode);
        $getNutriticsMiscellaneous = $this->getNutriticsMiscellaneous($filterCode);
        $allInfo = array_merge($getNutriticsEnergy, $getNutriticsMacro, $getNutriticsCarbohydrates, $getNutriticsFats, $getNutriticsMinerals, 
                                $getNutriticsVitamins, $getNutriticsFats, $getNutriticsMinerals, $getNutriticsVitamins, $getNutriticsOther, $getNutriticsMiscellaneous);

        return $allInfo;
    }
    
    public function getNutriticsReportUrl($filterCode) 
    {
        $nutriticsInfo = json_decode($this->nutriticsApi->getNutriticsInfo($filterCode), true);
        $reporturl = $nutriticsInfo[1]['reporturl'];
        return $reporturl;
    }

    /**
     * Get Energy info of Product by product's ifcCode or productSku
     * @param 
     * @return array
     */
    public function getNutriticsEnergy($filterCode) 
    {
        $nutriticsInfo = json_decode($this->nutriticsApi->getNutriticsInfo($filterCode, ['energyKcal', 'energyKj']), true);
        if (!is_array($filterCode)) {
            $return  = isset($nutriticsInfo[1]) ? $nutriticsInfo[1] : [] ;
        } else {
            $return = $nutriticsInfo;
        }
        
        return $return;
    }
    /**
     * Get Macronutrients info of Product by product's ifcCode or productSku
     * @param 
     * @return array
     */
    public function getNutriticsMacro($filterCode) 
    {
        $nutriticsInfo = json_decode($this->nutriticsApi->getNutriticsInfo($filterCode, ['carbohydrate', 'protein', 'fat', 'water', 'waterDr', 'alcohol']), true);
        if (!is_array($filterCode)) {
            $return  = isset($nutriticsInfo[1]) ? $nutriticsInfo[1] : [] ;
        } else {
            $return = $nutriticsInfo;
        }
        return $return;
    }
    /**
     * Get Carbohydrates info of Product by product's ifcCode or productSku
     * @param 
     * @return array
     */
    public function getNutriticsCarbohydrates($filterCode) 
    {
        $nutriticsInfo = json_decode($this->nutriticsApi->getNutriticsInfo($filterCode, ['starch','oligosaccharide','fibre','nsp','sugars','freesugars','glucose','galactose','fructose','sucrose','maltose','lactose']), true);
        if (!is_array($filterCode)) {
            $return  = isset($nutriticsInfo[1]) ? $nutriticsInfo[1] : [] ;
        } else {
            $return = $nutriticsInfo;
        }
        return $return;
    }
    /**
     * Get Fats (Lipid Components) info of Product by product's ifcCode or productSku
     * @param 
     * @return array
     */
    public function getNutriticsFats($filterCode) 
    {
        $nutriticsInfo = json_decode($this->nutriticsApi->getNutriticsInfo($filterCode, ['satfat','monos','cismonos','poly','n3poly','n6poly','cispoly','trans','cholesterol']), true);
        if (!is_array($filterCode)) {
            $return  = isset($nutriticsInfo[1]) ? $nutriticsInfo[1] : [] ;
        } else {
            $return = $nutriticsInfo;
        }
        return $return;
    }
    /**
     * Get Minerals and Trace Elements info of Product by product's ifcCode or productSku
     * @param 
     * @return array
     */
    public function getNutriticsMinerals($filterCode) 
    {
        $nutriticsInfo = json_decode($this->nutriticsApi->getNutriticsInfo($filterCode, ['sodium','potassium','chloride','calcium','phosphorus','magnesium','iron','zinc','copper','manganese','selenium','iodine']), true);
        if (!is_array($filterCode)) {
            $return  = isset($nutriticsInfo[1]) ? $nutriticsInfo[1] : [] ;
        } else {
            $return = $nutriticsInfo;
        }
        return $return;
    }
    /**
     * Get Vitamins info of Product by product's ifcCode or productSku
     * @param 
     * @return array
     */
    public function getNutriticsVitamins($filterCode) 
    {
        $nutriticsInfo1 = json_decode($this->nutriticsApi->getNutriticsInfo($filterCode, ['vita','retinol','carotene','vitd','vite','vitk','thiamin','riboflavin','niacineqv']), true);
        
        $nutriticsInfo2 = json_decode($this->nutriticsApi->getNutriticsInfo($filterCode, ['niacin','tryptophan','pantothenate','vitb6','folate','vitb12','biotin','vitc']), true);
        if (!is_array($filterCode)) {
            $return1  = isset($nutriticsInfo1[1]) ? $nutriticsInfo1[1] : [] ;
            $return2  = isset($nutriticsInfo2[1]) ? $nutriticsInfo2[1] : [] ;
            
            return array_merge($return1, $return2);
        } else {
            
            return array_merge($nutriticsInfo1, $nutriticsInfo2);
        }
    }
    /**
     * Get Other info of Product by product's ifcCode or productSku
     * @param 
     * @return array
     */
    public function getNutriticsOther($filterCode) 
    {
        $nutriticsInfo = json_decode($this->nutriticsApi->getNutriticsInfo($filterCode, ['gi','gl','caffeine']), true);
        if (!is_array($filterCode)) {
            $return  = isset($nutriticsInfo[1]) ? $nutriticsInfo[1] : [] ;
        } else {
            $return = $nutriticsInfo;
        }
        return $return;
    }
    /**
     * Get Miscellaneous info of Product by product's ifcCode or productSku
     * @param 
     * @return array
     */
    public function getNutriticsMiscellaneous($filterCode) 
    {
        $nutriticsInfo = json_decode($this->nutriticsApi->getNutriticsInfo($filterCode, ['allergens']), true);
        if (!is_array($filterCode)) {
            $return  = isset($nutriticsInfo[1]) ? $nutriticsInfo[1] : [] ;
        } else {
            $return = $nutriticsInfo;
        }
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
        $productQuery = $this->connection->select()->from(['nut_tbl' => self::TABLE_NUTRITICS_PRODUCT_ATTRIBUTE_VALUE],['nut_tbl.row_id'])->group('nut_tbl.row_id');
        $productIds = $this->connection->fetchCol($productQuery);

        $collection = $this->productCollectionFactory->create()->addAttributeToSelect('*');
        $collection->addAttributeToFilter('type_id', \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
        $collection->setOrder('entity_id','ASC');
        if ($productIds) {
            $collection->addAttributeToFilter('entity_id', ['nin'=>$productIds]);
        }
        $collection->getSelect()->limit(10);
        return $collection;
    }
}