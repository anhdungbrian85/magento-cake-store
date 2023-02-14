<?php
 
namespace X247Commerce\Nutritics\Controller\Adminhtml\Entity;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use X247Commerce\Nutritics\Service\NutriticsApi;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;
use X247Commerce\Nutritics\Helper\Config as ConfigHelper;
 
class MassSync extends Action
{
    const TABLE_NUTRITICS_PRODUCT_ATTRIBUTE_VALUE = 'nutritics_product_attribute_value';
    protected $filter;
    protected $prodCollFactory;
    protected $productRepository;
    protected $nutriticsApi;
    protected $resource;
    protected $connection;
    protected $logger;
    protected $configHelper;

    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $prodCollFactory,
        NutriticsApi $nutriticsApi,
        ResourceConnection $resource,
        LoggerInterface $logger,
        ConfigHelper $configHelper,
        ProductRepositoryInterface $productRepository
    )
    {
        parent::__construct($context);
        $this->filter = $filter;
        $this->prodCollFactory = $prodCollFactory;
        $this->productRepository = $productRepository;
        $this->nutriticsApi = $nutriticsApi;
        $this->resource = $resource;
        $this->configHelper = $configHelper;
        $this->logger = $logger;
        $this->connection = $resource->getConnection();
    }
 
    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException | \Exception
     */
    public function execute()
    {
        $selectedProducts = $this->filter->getCollection($this->prodCollFactory->create());
        $table = $this->resource->getTableName(self::TABLE_NUTRITICS_PRODUCT_ATTRIBUTE_VALUE);
        $productQuery = $this->connection->select()->from(['nut_tbl' => self::TABLE_NUTRITICS_PRODUCT_ATTRIBUTE_VALUE],['nut_tbl.row_id'])->group('nut_tbl.row_id');
        $productIds = $this->connection->fetchCol($productQuery);
        if ($productIds) {
            $selectedProducts->addAttributeToFilter('row_id', ['nin'=>$productIds]);
        }
        
        if (count($selectedProducts) > 0) {
            foreach ($selectedProducts as $product)
            {
                $nutricInfo = $this->getNutriticsInfo($product->getSku());

                if ($nutricInfo) {
                    $this->insertNutriticsInfo($product->getRowId(), $nutricInfo);
                }
            }
            
            $this->messageManager->addSuccess(__('A total of %1 record(s) have been synced.', $selectedProducts->getSize()));
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('catalog/product/index');
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
            return;
        }
    }

    public function getNutriticsInfo($filterCode) 
    {
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
}