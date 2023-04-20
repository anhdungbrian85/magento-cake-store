<?php

namespace X247Commerce\DeliveryPopUp\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UrlKeyStore extends Command
{
	protected $state;

	protected $resource;

	protected $yexthelper;

	function __construct(
		\Magento\Framework\App\State $state,
		\Magento\Framework\App\ResourceConnection $resource,
		\X247Commerce\Yext\Helper\YextHelper $yexthelper
	) {
		parent::__construct();
       	$this->state = $state;
       	$this->resource = $resource;
       	$this->yexthelper = $yexthelper;
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
    		$tableName = $this->resource->getTableName('amasty_amlocator_location');
    		$connection = $this->resource->getConnection();
			$name = $connection->select()
			    	->from(
				        ['ce' => 'amasty_amlocator_location'],
				        ['id','name']
			    	);
			$data = $connection->fetchAll($name);
    		foreach ($data as $value) {
	    		$connection->update(
				    $tableName,
				    ["url_key" =>$this->yexthelper->getUrlKeyFromName($value["name"])],
				    ['id = ?' => $value["id"]]
				);	
    		}
    	} catch (Exception $e) {
    		$output->writeln("<error>Error executing command: {$e->getMessage()}</error>");
    	}
    }
}