<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace X247Commerce\StoreLocatorSource\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use X247Commerce\StoreLocatorSource\Model\ResourceModel\LocatorSourceResolver;
use Magento\Framework\App\ResourceConnection;

class AssignSourceToLocation extends Command
{

    protected ResourceConnection $resource;
    protected $connection;
    protected LocatorSourceResolver $locatorSourceResolver;

    public function __construct(
        ResourceConnection $resource,
        LocatorSourceResolver $locatorSourceResolver
    )
    {        
        parent::__construct();
        $this->resource = $resource;
        $this->locatorSourceResolver = $locatorSourceResolver;
        $this->connection = $resource->getConnection();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {

        $sourceLinkTbl = $this->resource->getTableName('amasty_amlocator_location_source_link');
        $locationTbl = $this->resource->getTableName('amasty_amlocator_location');
        $souceTbl = $this->resource->getTableName('inventory_source'); 

        $locations = $this->connection->fetchAll(
            $this->connection->select()->from($locationTbl, ['id', 'name'])
        );
        
        foreach ($locations as $location) {
            $locationName = $location['name'];
            $locationId = $location['id'];

            $sourceCode = strtolower(str_replace([' ', '(', ')', '(ASDA)'], ['_', '', '', ''], $locationName));
            
            $source = $this->connection->fetchOne(
                $this->connection->select()
                    ->from($souceTbl)
                    ->where('source_code = ?', $sourceCode)
            );
            if ($source) {
                try {
                    $this->locatorSourceResolver->assignAmLocatorStoreToSource($locationId, $sourceCode);
                } catch (\Exception $e) {
                    $output->writeln('Cannot assign source to location'. $locationId .'|'. $sourceCode.' .'.$e->getMessage());
                }
            } 
            
        }

        $output->writeln("done!");
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("x247commerce:assignsourcetolocation");
        $this->setDescription("Assign Source To Location");
        $this->setDefinition([
           
        ]);
        parent::configure();
    }
}
