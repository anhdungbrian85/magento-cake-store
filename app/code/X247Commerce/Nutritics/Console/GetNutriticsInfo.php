<?php

namespace X247Commerce\Nutritics\Console;

use \Symfony\Component\Console\Command\Command;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use X247Commerce\Nutritics\Service\NutriticsApi;
use X247Commerce\Nutritics\Helper\Config as ConfigHelper;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputArgument;

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
             ->setDescription('Fetch Nutritics Info: add "--min-id=x --max-id=y" to limit range product id from x to y');

        $this->addArgument('sku', InputArgument::OPTIONAL, __('Type a product sku'));
        $this->addOption(
                'min-id',
                null,
                InputOption::VALUE_REQUIRED,
                'Min Id'
            );
        $this->addOption(
                'max-id',
                null,
                InputOption::VALUE_REQUIRED,
                'Max Id'
            );

        parent::configure();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('This process might take long time, please wait!');
        $sku = $input->getArgument('sku');
        $minId = $input->getOption('min-id');
        $maxId = $input->getOption('max-id');

        if ($minId && $maxId) {

            $this->processWithRangeProductId($minId, $maxId);
        } else {

            $this->processWithProductSku($sku);
        }

    }

     /**
     * Process Product Nutritic In Range Product Id not set value in nutritics_product_attribute_value
     * @param
     * @return Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function processWithRangeProductId($minId, $maxId)
    {
        try {
            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/nutricsclirangid.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);


            $products = $this->getProductCollectionInRangeProductId($minId, $maxId);

            if ($products->getSize() > 0) {
                $filterAttr = $this->configHelper->getProductApiAttributeFilter();

                $allNutricInfo = [];
                $allIfcCode = [];
                $allSkus = [];
                $allProductIds = [];

                foreach ($products as $product) {
                    $nutricInfo = [];
                    $allProductIds[] = $product->getRowId();
                    if ($filterAttr == ConfigHelper::NUTRITICS_CONFIG_API_ATTRIBUTE_IFC) {
                        if ($product->getIfcCode()) {
                            $allIfcCode[] = $product->getIfcCode();
                            $nutricInfo = $this->getNutriticsInfo($product->getIfcCode());
                        }
                    }   else {
                        $allSkus[] = $product->getSku();
                        // $logger->info($product->getSku());
                        $nutricInfo = $this->getNutriticsInfo($product->getSku());
                    }

                    if ($nutricInfo) {
                        $this->insertNutriticsInfo($product->getRowId(), $nutricInfo);
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
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

     /**
     * Process Product Nutritic with given sku or All product not set value in nutritics_product_attribute_value
     * @param
     * @return Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function processWithProductSku($sku = null)
    {

        try {
            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/nutricsclisku.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);

            $i = 1;
            while(true) {
                $products = $this->getProductCollection($i, $sku);

                if ($products->getSize() > ($i - 1)*100) {
                    $filterAttr = $this->configHelper->getProductApiAttributeFilter();

                    $allNutricInfo = [];
                    $allIfcCode = [];
                    $allSkus = [];
                    $allProductIds = [];

                    foreach ($products as $product) {
                        $nutricInfo = [];
                        $allProductIds[] = $product->getRowId();
                        if ($filterAttr == ConfigHelper::NUTRITICS_CONFIG_API_ATTRIBUTE_IFC) {
                            if ($product->getIfcCode()) {
                                $allIfcCode[] = $product->getIfcCode();
                                $nutricInfo = $this->getNutriticsInfo($product->getIfcCode());
                            }
                        }   else {
                            $allSkus[] = $product->getSku();
                            // $logger->info($product->getSku());
                            $nutricInfo = $this->getNutriticsInfo($product->getSku());
                        }

                        if ($nutricInfo) {
                            $this->insertNutriticsInfo($product->getRowId(), $nutricInfo);
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
                } else {
                    break;
                }

                $i++;
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Get Insert Product Nutritics Info to table nutritics_product_attribute_value
     * @param $productRowIds, array $nutricInfo
     * @return
     */
    public function insertNutriticsInfo($productRowIds, $nutricInfo)
    {
        $table = $this->resource->getTableName(self::TABLE_NUTRITICS_PRODUCT_ATTRIBUTE_VALUE);
        if ($productRowIds && $nutricInfo) {
            $insertData = [];
            foreach ($nutricInfo as $key => $value) {
                $nutrics = [];
                $allergens = [];
                $ingredients = [];
                if ($key != 'id') {
                    if (isset($value['name'])) {
                        if ($value['val']) {
                            $nutrics = ['row_id' => $productRowIds, 'attribute_code' => $key, 'attribute_name' => $value['name'], 'value' => $value['val'], 'attribute_unit' => $value['unit'],
                                        'percent_ri' => $value['percentRI']];
                        }

                    }
                    if (isset($value['contains'])) {
                        $allergens = ['row_id' => $productRowIds, 'attribute_code' => $key, 'attribute_name' => $key, 'value' => json_encode($value), 'attribute_unit' => '',
                        'percent_ri' => ''];
                    }
                    if ($key == 'quid' && isset($nutricInfo[$key])) {
                        $ingredients = ['row_id' => $productRowIds, 'attribute_code' => $key, 'attribute_name' => $key, 'value' => $value, 'attribute_unit' => '',
                        'percent_ri' => ''];
                    }
                    if (!empty($nutrics) || !empty($allergens) || !empty($ingredients)) {
                        $insertData[] = array_merge($nutrics, $allergens, $ingredients);
                    }
                }

            }

            if ($insertData) {
                return $this->connection->insertMultiple($table, $insertData);
            }
        }
    }

    /**
     * Get Insert Product Nutritics Info to table nutritics_product_attribute_value
     * @param array $productRowIds, array $nutricInfo
     * @return
     */
    public function insertMultiNutriticsInfo($productRowIds, $nutricInfo)
    {
        try {
            $table = $this->resource->getTableName(self::TABLE_NUTRITICS_PRODUCT_ATTRIBUTE_VALUE);

            $insertData = [];

            for ($i=0; $i < count($productRowIds); $i++)
            {

                foreach ($nutricInfo[$i + 1] as $key => $value) {
                    $nutrics = [];
                    $allergens = [];
                    if ($key != 'id') {
                        if (isset($value['name'])) {
                            if ($value['val']) {
                                $nutrics = ['row_id' => $productRowIds[$i], 'attribute_code' => $key, 'attribute_name' => $value['name'], 'value' => $value['val'], 'attribute_unit' => $value['unit'],
                                            'percent_ri' => $value['percentRI']];
                            }

                        }
                        if (isset($value['contains'])) {
                            $allergens = ['row_id' => $productRowIds[$i], 'attribute_code' => $key, 'attribute_name' => $key, 'value' => json_encode($value), 'attribute_unit' => '',
                            'percent_ri' => ''];
                        }
                        if (!empty($nutrics) || !empty($allergens)) {
                            $insertData[] = array_merge($nutrics, $allergens);
                        }
                    }

                }
            }
            // var_dump($insertData);die();
            if ($insertData) {
                return $this->connection->insertMultiple($table, $insertData);
            }
                return null;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

    }

    public function getNutriticsInfo($filterCode)
    {
        // $getNutriticsEnergy = $this->getNutriticsEnergy($filterCode);
        // $getNutriticsMacro = $this->getNutriticsMacro($filterCode);
        // $getNutriticsCarbohydrates = $this->getNutriticsCarbohydrates($filterCode);
        // $getNutriticsFats = $this->getNutriticsFats($filterCode);
        // $getNutriticsMinerals = $this->getNutriticsMinerals($filterCode);
        // $getNutriticsVitamins = $this->getNutriticsVitamins($filterCode);
        // $getNutriticsOther = $this->getNutriticsOther($filterCode);
        // $getNutriticsMiscellaneous = $this->getNutriticsMiscellaneous($filterCode);
        // $return = array_merge($getNutriticsEnergy, $getNutriticsMacro, $getNutriticsCarbohydrates, $getNutriticsFats, $getNutriticsMinerals,
        //                         $getNutriticsVitamins, $getNutriticsFats, $getNutriticsMinerals, $getNutriticsVitamins, $getNutriticsOther, $getNutriticsMiscellaneous);

        $params = ['energyKcal', 'energyKj', 'carbohydrate', 'protein', 'fat', 'water', 'waterDr', 'alcohol', 'starch','oligosaccharide','fibre','nsp','sugars','freesugars','glucose','galactose','fructose','sucrose','maltose','lactose', 'satfat','monos','cismonos','poly','n3poly','n6poly','cispoly','trans','cholesterol', 'sodium','potassium','chloride','calcium','phosphorus','magnesium','iron','zinc','copper','manganese','selenium','iodine', 'vita','retinol','carotene','vitd','vite','vitk','thiamin','riboflavin','niacineqv', 'niacin','tryptophan','pantothenate','vitb6','folate','vitb12','biotin','vitc', 'gi','gl','caffeine', 'allergens', 'quid'];
        $nutriticsInfo = json_decode($this->nutriticsApi->getNutriticsInfo($filterCode, $params), true);
        // var_dump($nutriticsInfo);die();
        if (!is_array($filterCode)) {
            $return  = isset($nutriticsInfo[1]) ? $nutriticsInfo[1] : [] ;
        } else {
            $return = $nutriticsInfo;
        }
        return $return;
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
    public function getProductCollection($page, $sku = null)
    {
        $table = $this->resource->getTableName(self::TABLE_NUTRITICS_PRODUCT_ATTRIBUTE_VALUE);
        $productQuery = $this->connection->select()->from(['nut_tbl' => self::TABLE_NUTRITICS_PRODUCT_ATTRIBUTE_VALUE],['nut_tbl.row_id'])->group('nut_tbl.row_id');
        $productIds = $this->connection->fetchCol($productQuery);

        $collection = $this->productCollectionFactory->create()->addAttributeToSelect('*');
        $collection->addAttributeToFilter('type_id', \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
        $collection->setOrder('row_id','ASC');

        if ($sku) {
            $collection->addAttributeToFilter('sku', $sku);
        }
        if ($productIds) {
            $collection->addAttributeToFilter('row_id', ['nin'=>$productIds]);
        }
        $collection->setPageSize(100);
        $collection->setCurPage($page);
        return $collection;
    }
    /**
     * Get Product Collection In Range Product Id not set value in nutritics_product_attribute_value
     * @param
     * @return Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getProductCollectionInRangeProductId($minId, $maxId)
    {
        $table = $this->resource->getTableName(self::TABLE_NUTRITICS_PRODUCT_ATTRIBUTE_VALUE);
        $productQuery = $this->connection->select()->from(['nut_tbl' => self::TABLE_NUTRITICS_PRODUCT_ATTRIBUTE_VALUE],['nut_tbl.row_id'])->group('nut_tbl.row_id');
        $productIds = $this->connection->fetchCol($productQuery);

        $collection = $this->productCollectionFactory->create()->addAttributeToSelect('*');
        $collection->addAttributeToFilter('type_id', \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
        $collection->addAttributeToFilter('entity_id', array('from' => $minId, 'to' => $maxId));
        $collection->setOrder('row_id','ASC');

        if ($productIds) {
            $collection->addAttributeToFilter('row_id', ['nin'=>$productIds]);
        }

        return $collection;
    }
}
