<?php
 
namespace X247Commerce\StoreLocator\Console;

use \Symfony\Component\Console\Command\Command;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputArgument;

class RemoveDuplicateEntry extends Command
{
    const TABLE_DELIVERY_POSTCODE = 'store_location_delivery_area';

    protected ResourceConnection $resource;
    protected $connection;
    protected LoggerInterface $logger;

    public function __construct(
    	ResourceConnection $resource,
    	LoggerInterface $logger
    ) {
        parent::__construct();
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->logger = $logger;
    }

    /**
     * @inherit
     * **/
    protected function configure()
    {
        $this->setName('x247commerce:delivery-postcode:remove-duplicate')
             ->setDescription('Remove duplicate delivery postcode');

        parent::configure();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {

        $output->writeln('Working on remove duplicate entries...');	
        
        $deliveryTable = $this->connection->getTableName('store_location_delivery_area');
        $query = $this->connection->select()
                        ->from($deliveryTable, ['ids' => 'group_concat(id)', 'postcode', 'matching_strategy', 'store_id' , 'count' => 'COUNT(id)'])
                        ->group(['postcode', 'matching_strategy', 'store_id'])
                        ->having('count > 1');
        $duplicateEntries = $this->connection->fetchAll($query);     
        If (count($duplicateEntries)) {
            $output->writeln('There are some entries with duplicate data: ');
            foreach ($duplicateEntries as $entry) {
                $entryIds = $entry['ids'];
                $entryPostcode = $entry['postcode'];
                $entryMatchingStrategy = $entry['matching_strategy'];
                $entryStore = $entry['store_id'];

                $output->writeln("Entries with id $entryIds have same: postcode: $entryPostcode | matching_strategy: $entryMatchingStrategy | store:  $entryStore"); 
            }
            $output->writeln('Do nothing now...'); 
        }
    }

}