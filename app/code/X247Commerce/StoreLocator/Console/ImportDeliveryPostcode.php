<?php
 
namespace X247Commerce\StoreLocator\Console;

use \Symfony\Component\Console\Command\Command;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Magento\Framework\Filesystem\Driver\File as FileDriver;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

class ImportDeliveryPostcode extends Command
{
    const TABLE_DELIVERY_POSTCODE = 'store_location_delivery_area';
    const DELIVERY_FILE_FOLDER = 'import/';
    const DELIVERY_FILE_NAME = 'delivery-postcodes.csv';
    const DELIVERY_FILE_COLUMNS = ['ID', 'Status', 'Matching Strategy', 'Name', 'Postcode', 'Store'];


    protected ResourceConnection $resource;
    protected $connection;
    protected LoggerInterface $logger;
    protected FileDriver $fileDriver;
    protected Filesystem $filesystem;
    protected $dataPostcode;
    protected $file;

    public function __construct(
    	ResourceConnection $resource,
    	LoggerInterface $logger,
    	FileDriver $fileDriver,
    	Filesystem $filesystem
    ) {
        parent::__construct();
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->logger = $logger;
        $this->fileDriver = $fileDriver;
        $this->filesystem = $filesystem;
    }

    /**
     * @inherit
     * **/
    protected function configure()
    {
        $this->setName('x247commerce:delivery-postcode:import')
             ->setDescription('Import delivery postcode');

        $this->addArgument('file', InputArgument::OPTIONAL, __('Type a custom csv file path'));
        $this->addOption(
                'skip-clean',
                null,
                InputOption::VALUE_NONE,
                'Skip Remove All Old Record before Import New Record'
            );
        parent::configure();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/not-exits-store.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('Not Exists Store:');

        $output->writeln('Importing...');	
        $file = $input->getArgument('file');
        $skipRemoveOldRecord = $input->getOption('skip-clean');

        if (!$file) {
        	$file = self::DELIVERY_FILE_NAME;
        }
        $deliveryTable = $this->connection->getTableName('store_location_delivery_area');

        $filePath = $this->filesystem
                ->getDirectoryRead(DirectoryList::VAR_DIR)
                ->getAbsolutePath(). self::DELIVERY_FILE_FOLDER. $file;

       	if ($this->fileDriver->isExists($filePath)) {
       		$this->file = $filePath;
       		$dataPostcode = $this->readDataPostcodeFromFile();

       		if (!empty($dataPostcode) && !$skipRemoveOldRecord) {
       			$this->connection->delete($deliveryTable);
       		}
       		foreach ($dataPostcode as $rowPostcode) {
       			if ($rowPostcode[0] == "ID") {
       				continue;
       			}
                if ($rowPostcode[0] == "Status") {
                    continue;
                }
       			if (empty($rowPostcode[4])) {
       				continue;
       			}
                if (!$this->findStoreLocation($rowPostcode[4])) {
                    $logger->info(print_r($rowPostcode[4], true));
                    continue;
                }

       			$rowInsert = [
       				'name' => $rowPostcode[2],
       				'postcode' => $rowPostcode[3],
       				'status' => (strpos($rowPostcode[0], 'White') !== FALSE) ? 1 : 0, 
       				'matching_strategy' => $rowPostcode[1],
       				'store_id' => $this->findStoreLocation($rowPostcode[4])
       			];
                
       			try {
       				$this->connection->insert(
       					$deliveryTable,
       					$rowInsert
       				);
       			} catch (\Exception $e) {
       				$output->writeln('Cannot insert row '. $rowPostcode[2] . ' - '. $e->getMessage());
       			}
       		}

       	}	else {
       		throw new \Exception('Could not found the input file. Please upload file to var/import then specific the file name in command! php bin/magento x247commerce:delivery-postcode:import file_name.csv');
       	}
    }

    protected function findStoreLocation($storeNameCsv)
    {	
    	$locationTbl = $this->connection->getTableName('amasty_amlocator_location');
        $storeNameCsv = implode(' ',array_unique(explode(' ', $storeNameCsv)));
        $storeNameCsv = trim($storeNameCsv);
        if (strpos($storeNameCsv, 'Cake Box') !== FALSE) {
            $storeName = $storeNameCsv;
        } else {
            $storeName = "Cake Box $storeNameCsv";
        }
        
    	return $this->connection->fetchOne(
    		"SELECT ID FROM $locationTbl WHERE name='$storeName';"
    	);
    }

    protected function readDataPostcodeFromFile() 
    {
    	if (!$this->dataPostcode) {
			if (($handle = fopen($this->file, "r")) !== FALSE) {
			    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
			        $dataPostcode[] = $data;
			    }
			    fclose($handle);
			}
			$this->dataPostcode = $dataPostcode;
    	}
    	return $this->dataPostcode;
    }
}