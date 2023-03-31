<?php
namespace X247Commerce\Products\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;

class ChangeAttributeSet extends Command
{
	protected $resourceConnection;
	protected $output;
	protected $categoryCollectionFactory;

	public function __construct(
		ResourceConnection $resourceConnection,
		CategoryCollectionFactory $categoryCollectionFactory,
		\Symfony\Component\Console\Output\ConsoleOutput $output,
	) {
		$this->resourceConnection = $resourceConnection;
		$this->categoryCollectionFactory = $categoryCollectionFactory;
		$this->output = $output;
		parent::__construct();
	}
		
	protected function configure()
	{
		$this->setName('x247commerce:catalog:change-attribute-set');
		$this->setDescription('Change products attribute set.');
		 
		parent::configure();
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{	
		$this->changeAllToCakeAS();
        $this->output->writeln("Done! Please reindex and flush cache!");
	}

	protected function changeAllToCakeAS()
	{
		$resource = $this->resourceConnection;
		$connection = $resource->getConnection();
		$cakeAttributeSet = $connection->fetchOne("SELECT attribute_set_id FROM eav_attribute_set where attribute_set_name = 'Cake' and entity_type_id = 4")
		;
		$accessoryAttributeSet = $connection->fetchOne("SELECT attribute_set_id FROM eav_attribute_set where attribute_set_name = 'Accessory' and entity_type_id = 4")
		;
		
		$accessoryCate = $this->categoryCollectionFactory->create()
						->addAttributeToSelect('*')
						->addAttributeToFilter('url_key', 'accessories')
						->getFirstItem();

		if ($accessoryCate->getId()) {
			$allAccessoryProductIds = $accessoryCate->getProductCollection()->getAllIds();
		}

		try {
			$accessoryProductIdsStr = '';
			if (!empty($allAccessoryProductIds)) {
				$accessoryProductIdsStr = implode(',', $allAccessoryProductIds);
			}

			// Change to Cake
			$connection->query(
					"UPDATE catalog_product_entity SET attribute_set_id = $cakeAttributeSet WHERE entity_id NOT IN($accessoryProductIdsStr);" 
				);

			// Change to Accessory
			$connection->query(
				"UPDATE catalog_product_entity SET attribute_set_id = $accessoryAttributeSet WHERE entity_id IN($accessoryProductIdsStr);" 
			);
		} catch (\Exception $e) {
			$this->output->writeln($e->getMessage());
		}

	}
	
}