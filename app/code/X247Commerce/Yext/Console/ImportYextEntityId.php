<?php
namespace X247Commerce\Yext\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\Csv as CsvReader;
use Magento\Store\Model\StoreManagerInterface;
use X247Commerce\Yext\Model\YextAttribute;

class ImportYextEntityId extends Command
{
    const DATA_FILE_PATH = '/import/yext/location_yext_id.csv';
    protected const AMASTY_AMLOCATOR_STORE_ATTRIBUTE = 'amasty_amlocator_store_attribute';

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

    public function __construct(
        DirectoryList $directoryList,
        CsvReader $csvReader,
        StoreManagerInterface $storeManager,
        \Magento\Framework\App\State $state,
        \Amasty\Storelocator\Model\ResourceModel\Location\CollectionFactory $locationCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        YextAttribute $yextAttribute,
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
        $this->connection = $resource->getConnection();
        $this->resource = $resource;
    }

    protected function configure()
    {
        $this->setName('import:yextentityid')
             ->setDescription('Import Yext Entity Id');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        echo __("Work can take a long time. Please wait.");
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        $this->import();
    }

    private function import() 
    {
        try {
            $storeId = $this->storeManager->getStore()->getId();
            $csvFile = $this->getCsvFile();

            $data = [];
            $storeIdZip = $this->getStoreCollection();

            foreach ($csvFile as $key => $csvData) {

                if ($key < 1) {
                    continue;
                }

                $yext_entity_id = $csvData[0];
                $post_code = $csvData[3];
                if (in_array($post_code, $storeIdZip)) {
                    $data[array_keys($storeIdZip, $post_code)[0]] = $yext_entity_id;
                }
            }

            $this->insertBulkData($data);
            echo __("\nSuccess.\n");
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
        $pubMediaDir = $this->directoryList->getPath('var');
        $csvFile = $pubMediaDir . self::DATA_FILE_PATH;
        if (!file_exists($csvFile)) {
            $csvFile = $this->directoryList->getRoot() . self::DATA_FILE_PATH;
        }
        $fileData = $this->csvReader->getData($csvFile);

        return $fileData;
    }

    public function getStoreCollection()
    {
        $store = [];
        $locations = $this->locationCollectionFactory->create();
        foreach ($locations as $location)
        {
            $store[$location->getId()] = $location->getZip();
        }

        return $store;
    }
}