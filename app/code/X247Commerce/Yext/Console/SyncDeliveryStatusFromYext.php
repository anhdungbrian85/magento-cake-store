<?php
 
namespace X247Commerce\Yext\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use X247Commerce\Yext\Service\YextApi;
use X247Commerce\Yext\Helper\YextHelper as ConfigHelper;
use X247Commerce\Yext\Model\YextAttribute;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Amasty\Storelocator\Model\ResourceModel\Location\CollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\State;

class SyncDeliveryStatusFromYext extends Command
{
    protected $yextApi;
    protected $searchCriteriaBuilder;
    protected $locationCollectionFactory;
    protected $resource;
    protected $connection;
    protected $configHelper;
    protected $logger;
    protected $yextAttribute;
    protected $state;

    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CollectionFactory $locationCollectionFactory,
        YextApi $yextApi,
        ResourceConnection $resource,
        LoggerInterface $logger,
        YextAttribute $yextAttribute,
        ConfigHelper $configHelper,
        State $state
    ) {
        parent::__construct();
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->locationCollectionFactory = $locationCollectionFactory;
        $this->yextApi = $yextApi;
        $this->resource = $resource;
        $this->configHelper = $configHelper;
        $this->logger = $logger;
        $this->yextAttribute = $yextAttribute;
        $this->connection = $resource->getConnection();
        $this->state = $state;
    }

    protected function configure()
    {
        $this->setName('yext:pickupdelivery')
             ->setDescription('Sync Location Pickup and Delivery Status Info from Yext add "--min-id=x --max-id=y" to limit range store location id from x to y');

        $this->addArgument('locationid', InputArgument::OPTIONAL, __('Type a Location Id'));
        $this->addOption(
                'min-id',
                null,
                InputOption::VALUE_REQUIRED,
                'Min Id'
            );
        $this->addOption(
                'max-id',
                null,
                InputOption::VALUE_REQUIRED,
                'Max Id'
            );

        parent::configure();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('This process might take long time, please wait!');
        $locationid = $input->getArgument('locationid');
        $minId = $input->getOption('min-id');
        $maxId = $input->getOption('max-id');

        if ($minId && $maxId) {            
            $this->processWithRangelocationId($minId, $maxId);
        } else {
            echo "Please Enter Min and Max Id";
        }
    }

     /**
     * Process location In Range location Id
     * @param 
     * @return Magento\Catalog\Model\ResourceModel\location\Collection
     */
    public function processWithRangelocationId($minId, $maxId)
    {
        try {            
            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/locationrangid.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);

            $allYextEntityIdValue = array_column($this->yextAttribute->getAllYextEntityIdValue(),'value', 'store_id');
            $yextEntityIds = $this->getYextEnityIdCollection($minId, $maxId);
            
            $filterParams = [];
            foreach ($yextEntityIds as $id) {
                $filterParams['$or'][] = ['entityId' => ['$eq' => $id]];
            }

            $listResponse = json_decode($this->yextApi->getList(['filter'=> json_encode($filterParams)]), true);
            // var_dump($listResponse);die();
            
            $tableName = $this->resource->getTableName('amasty_amlocator_location');
            
            foreach ($listResponse['response']['entities'] as $locationData) {
                $delivery = ['enable_delivery' => 0];
                $inStorePickup = ['curbside_enabled' => 0];
                $locationId = (int) array_search($locationData['meta']['id'], $allYextEntityIdValue);  
                echo 'Location id: ',$locationId,"\n";      
                echo 'Location yext_entity_id: '.$locationData['meta']['id']."\n";      
                if (isset($locationData['pickupAndDeliveryServices'])) {
                    if (in_array('DELIVERY', $locationData['pickupAndDeliveryServices'])) {
                        $delivery = ['enable_delivery' => 1];
                    } 
                    if (in_array('IN_STORE_PICKUP', $locationData['pickupAndDeliveryServices'])) {
                        $inStorePickup = ['curbside_enabled' => 1];
                    }                  
                }
                $updateData = array_merge($delivery, $inStorePickup);
                echo $updateData['enable_delivery'] ? "Enable DELIVERY"."\n" : "Disable DELIVERY"."\n";
                echo $updateData['curbside_enabled'] ? "Enable IN_STORE_PICKUP"."\n" : "Disable IN_STORE_PICKUP"."\n";
                echo "\n";
                $query = $this->connection->update($tableName, $updateData, ['id = ?' => (int)$locationId]);
            }
            echo __('A total of %1 record(s) have been modified.', count($yextEntityIds))."\n";
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Get YextEnityId Collection with location Id in range minLocationId to maxLocationId
     * @param 
     * @return array yext_entity_id
     */
    public function getYextEnityIdCollection($minLocationId, $maxLocationId)
    {
        $arrayIds = range($minLocationId, $maxLocationId);

        $yextEntityIds = $this->yextAttribute->getYextEntityIdByLocationId($arrayIds);
        return $yextEntityIds;
    }
}