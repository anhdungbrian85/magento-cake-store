<?php
namespace X247Commerce\Yext\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\Csv as CsvReader;
use Magento\Store\Model\StoreManagerInterface;
use X247Commerce\Yext\Model\YextAttribute;

class ImportYextEntityId extends Command
{
    const DATA_FILE_PATH = '/import/yext/location_yext_id.csv';
    protected const AMASTY_AMLOCATOR_STORE_ATTRIBUTE = 'amasty_amlocator_store_attribute';
    protected const NAME_ARGUMENT = 'name';
    protected const POSTCODE_ARGUMENT = 'postcode';

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
    * @var CsvReader
    */
    private $csvReader;

    private $storeManager;

    /** 
     * @var \Magento\Framework\App\State 
     * **/
    private $state;

    private $logger;

    protected $locationCollectionFactory;

    protected $connection;

    protected $resource;

    private $yextAttribute;

    private $output;

    public function __construct(
        DirectoryList $directoryList,
        CsvReader $csvReader,
        StoreManagerInterface $storeManager,
        \Magento\Framework\App\State $state,
        \Amasty\Storelocator\Model\ResourceModel\Location\CollectionFactory $locationCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        YextAttribute $yextAttribute,
        \Symfony\Component\Console\Output\ConsoleOutput $output,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct();
        $this->directoryList = $directoryList;
        $this->csvReader = $csvReader;
        $this->storeManager = $storeManager;
        $this->state = $state;
        $this->logger = $logger;
        $this->locationCollectionFactory = $locationCollectionFactory;
        $this->yextAttribute = $yextAttribute;
        $this->output = $output;
        $this->connection = $resource->getConnection();
        $this->resource = $resource;
    }

    protected function configure()
    {
        $this->setName('x247commerce:import:yextentityid')
             ->setDescription('Import Yext Entity Id: default by Name, add " postcode" to import by Postcode')
             ->addArgument(
                self::POSTCODE_ARGUMENT,
                InputArgument::OPTIONAL,
                'Import By Post Code'
            )
             ;

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $importBy = self::NAME_ARGUMENT;
        $flag = "Name";
        $post_code = $input->getArgument(self::POSTCODE_ARGUMENT);
        if ($post_code == self::POSTCODE_ARGUMENT) {
            $importBy = self::POSTCODE_ARGUMENT;
            $flag = 'Post Code';
        }
        $this->output->writeln("Work can take a long time. Please wait.");
        $this->output->writeln("Import By: ".$flag);
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        $this->import($importBy);
    }

    private function import($importBy) 
    {
        try {
            $storeId = $this->storeManager->getStore()->getId();
            $csvFile = $this->getCsvFile();

            $data = [];

            $yextIdIndex = 0;
            $postCodeIndex = 0;
            $locationNameIndex = 0;
            $store = [];
            if ($importBy == self::POSTCODE_ARGUMENT) {
                $store = $this->getStoreZipCollection();
            } else {
                $store = $this->getStoreNameCollection();
            }
            if ($csvFile) {
                foreach ($csvFile as $key => $csvData) {

                    if ($key < 1) {
                        $yextIdIndex = (int) array_search('Entity ID', $csvData);
                        $locationNameIndex = (int) array_search('Name', $csvData);
                        $postCodeIndex = (int) array_search('Address > Postal Code', $csvData);
                        continue;
                    }

                    $yext_entity_id = $csvData[$yextIdIndex];
                    $post_code = $csvData[$postCodeIndex];
                    if ($importBy == self::POSTCODE_ARGUMENT) {
                        $locationImprtIndex = $csvData[$postCodeIndex];
                    } else {
                        $locationImprtIndex = $csvData[$locationNameIndex];
                    }
                    if (in_array($locationImprtIndex, $store)) {
                        $data[array_search($locationImprtIndex, $store)] = $yext_entity_id;
                    }
                }
                $this->insertBulkData($data);
                $this->output->writeln("Success");
            }

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function insertBulkData($insert)
    {
        $yextAttribute = $this->yextAttribute->getYextEntityAttributeId();
        $data = [];
        foreach ($insert as $key => $value) {        
          $data[] = [
            'attribute_id' => $yextAttribute,
            'store_id' => $key,
            'value' => $value
          ];
        }
        try {
            $tableName = $this->resource->getTableName(self::AMASTY_AMLOCATOR_STORE_ATTRIBUTE);
            // return $this->connection->insertMultiple($tableName, $data);
            $this->connection->insertOnDuplicate($tableName, $data, ['value']);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    private function getCsvFile()
    {
        $varDir = $this->directoryList->getPath('var');
        $csvFile = $varDir . self::DATA_FILE_PATH;
        if (!file_exists($csvFile)) {
            $csvFile = $this->directoryList->getRoot() . "/app/code/X247Commerce/Yext" . self::DATA_FILE_PATH;            
        }
        if (!file_exists($csvFile)) {
            $this->output->writeln('File does not exist!');
            return [];          
        }
        $fileData = $this->csvReader->getData($csvFile);

        return $fileData;
    }

    public function getStoreZipCollection()
    {
        $store = [];
        $locations = $this->locationCollectionFactory->create();
        foreach ($locations as $location)
        {
            $store[$location->getId()] = $location->getZip();
        }

        return $store;
    }
    public function getStoreNameCollection()
    {
        $store = [];
        $locations = $this->locationCollectionFactory->create();
        foreach ($locations as $location)
        {
            $store[$location->getId()] = $location->getName();
        }

        return $store;
    }
}