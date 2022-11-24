<?php

namespace X247Commerce\DeliveryPopUp\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UrlKeyStore extends Command
{
	protected $state;

	protected $collection;

	protected $locationCollectionFactory;

	protected $locationFactory;

	function __construct(
		\Magento\Framework\App\State $state,
		\Amasty\Storelocator\Model\ResourceModel\Location\Collection $collection,
		\Amasty\Storelocator\Model\LocationFactory $locationFactory,
		\Amasty\Storelocator\Model\ResourceModel\Location\CollectionFactory $locationCollectionFactory
	) {
		parent::__construct();
       	$this->state = $state;
       	$this->collection = $collection;
       	$this->locationCollectionFactory = $locationCollectionFactory;
       	$this->locationFactory = $locationFactory;
	}

	protected function configure()
    {
        $this->setName('x247commerce:genurlkey');
        $this->setDescription('This command is used to gen url for location store.');
        parent::configure();
    }

     protected function execute(InputInterface $input, OutputInterface $output)
    {
    	$this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
    	try {
    		$collectionStore = $this->locationFactory->create()->getCollection();

    		foreach ($collectionStore as $value) {
    			$data = $value->getData();
    			$nameStore = $data['name'];
    			$url_key = trim($nameStore,")");
    			$url_key = strtolower($url_key);
    			$url_key = str_replace('(', '', $url_key);
    			$url_key = str_replace(' ', '_', $url_key);
    			$value->setData('url_key',$url_key)->save();
    		}
    	} catch (Exception $e) {
    		$output->writeln("<error>Error executing command: {$e->getMessage()}</error>");
    	}
    }
}