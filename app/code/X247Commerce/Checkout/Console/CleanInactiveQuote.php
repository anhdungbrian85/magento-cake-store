<?php

namespace X247Commerce\Checkout\Console;

use \Symfony\Component\Console\Command\Command;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputArgument;

class CleanInactiveQuote extends Command
{
    const QUOTE_TABLE = 'quote';

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
    }

    protected function configure()
    {
        $this->setName('x247commerce:clean-inactive-quote')
             ->setDescription('Clean inative quote');
        parent::configure();
    }

    /**
     * @var InputInterface $input, OutputInterface $output
     * @return void
     * */
    public function execute(InputInterface $input, OutputInterface $output)
    {
    	$this->cleanNotExistCustomerQuote();

    	

        $output->writeln('Clean inative quote executed');


    }

    
    private function cleanNotExistCustomerQuote()
    {
    	$quoteTbl = $this->resource->getTableName(self::QUOTE_TABLE);
    	$this->connection->delete($quoteTbl, "customer_id not in (SELECT entity_id FROM customer_entity)");

        $query = $this->connection->select()
                                    ->from($quoteTbl, 'MAX(entity_id)')
                                    ->group('customer_id')
                                    ->where('is_active = 1');
        $customerIds = $this->connection->fetchCol($query);
        $this->connection->delete($quoteTbl, ['is_active = ?' => 1, 'entity_id NOT IN (?)' => $customerIds]);
    }
}
