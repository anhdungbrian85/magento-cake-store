<?php
namespace X247Commerce\Products\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface;
use Magento\InventoryCatalogApi\Model\GetProductTypesBySkusInterface;
use Magento\Framework\DB\Adapter\DuplicateException;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\Inventory\Model\ResourceModel\SourceItem;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;

class AssignAllProductToAllSource extends Command
{
	protected $resourceConnection;

	protected $isSourceItemManagementAllowedForProductType;

	protected $getProductTypesBySkus;

	protected $productCollectionFactory;

	protected $searchCriteriaBuilder;

	protected $sourceRepository;

	public function __construct(
		SearchCriteriaBuilder $searchCriteriaBuilder,
		ResourceConnection $resourceConnection,
		SourceRepositoryInterface $sourceRepository,
		GetProductTypesBySkusInterface $getProductTypesBySkus,
		IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType,
		ProductCollectionFactory $productCollectionFactory
	) {
		$this->searchCriteriaBuilder = $searchCriteriaBuilder;
		$this->sourceRepository = $sourceRepository;
		$this->resourceConnection = $resourceConnection;
		$this->isSourceItemManagementAllowedForProductType = $isSourceItemManagementAllowedForProductType;
		$this->getProductTypesBySkus = $getProductTypesBySkus;
		$this->productCollectionFactory = $productCollectionFactory;
		parent::__construct();
	}
		
	protected function configure()
	{
		$this->setName('x247commerce:inventory-source:products');
		$this->setDescription('Assign all products to all source.');
		 
		parent::configure();
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/custom111.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
		$sourceList = $this->getSourcesList();
		$i = 1;
        while(true) {
            $products = $this->getProductCollection($i);
            $products->load();
            $logger->info($products->getSelect()->__toString());
            if (!empty($sourceList) && $products->getSize() > ($i - 1)*100) {
              //   foreach ($products as $product) {
		            // $this->saveSourceItem([$product->getSku()], $sourceList);
              //   }
            } else {
                break;
            }

            $i++;
        }

        return;
	}

	public function getProductCollection($page)
    {
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter('type_id', \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
        $collection->setPageSize(100);
        $collection->setCurPage($page);
        return $collection;
    }

	protected function getSourcesList()
    {
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $sourceList = [];
        try {
            $sourceData = $this->sourceRepository->getList($searchCriteria);
            if ($sourceData->getTotalCount()) {
                foreach ($sourceData->getItems() as $source) {
                    $sourceList[] = $source->getData('source_code');
                }
            }
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
        return $sourceList;
    }

    protected function saveSourceItem(array $skus, array $sourceCodes)
    {
        $types = $this->getProductTypesBySkus->execute($skus);
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName(SourceItem::TABLE_NAME_SOURCE_ITEM);

        foreach ($types as $sku => $type) {
            if ($this->isSourceItemManagementAllowedForProductType->execute($type)) {
                foreach ($sourceCodes as $sourceCode) {
                    try {
                        $connection->insert($tableName, [
                            SourceItemInterface::SOURCE_CODE => $sourceCode,
                            SourceItemInterface::SKU => $sku,
                            SourceItemInterface::QUANTITY => 100,
                            SourceItemInterface::STATUS => SourceItemInterface::STATUS_IN_STOCK,
                        ]);
                    } catch (DuplicateException $e) {
                        continue;
                    }
                }
            }
        }
    }
}