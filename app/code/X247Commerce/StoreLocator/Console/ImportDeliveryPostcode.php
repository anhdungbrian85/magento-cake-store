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
        parent::configure();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {

        $output->writeln('Importing...');	
        $file = $input->getArgument('file');
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

       		if (!empty($dataPostcode)) {
       			$this->connection->delete($deliveryTable);
       		}
       		foreach ($dataPostcode as $rowPostcode) {
       			if ($rowPostcode[0] == "ID") {
       				continue;
       			}

       			if (empty($rowPostcode[5])) {
       				continue;
       			}

       			$rowInsert = [
       				'name' => $rowPostcode[3],
       				'postcode' => $rowPostcode[4],
       				'status' =>  $rowPostcode[1] == 'WhiteListed' ? 1 : 0, 
       				'matching_strategy' => $rowPostcode[2],
       				'store_id' => $this->findStoreLocation($rowPostcode[5])
       			];
       			try {
       				$this->connection->insert(
       					$deliveryTable,
       					$rowInsert
       				);
       			} catch (\Exception $e) {
       				$output->writeln('Cannot insert row '. $rowPostcode[3] . ' - '. $e->getMessage());
       			}
       		}

       	}	else {
       		throw new \Exception('Could not found the input file. Please upload file to var/import then specific the file name in command! php bin/magento x247commerce:delivery-postcode:import file_name.csv');
       	}
    }

    protected function findStoreLocation($oldStoreName)
    {	
    	$locationTbl = $this->connection->getTableName('amasty_amlocator_location');
    	return $this->connection->fetchOne(
    		"SELECT ID FROM $locationTbl WHERE name='Cake Box $oldStoreName';"
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