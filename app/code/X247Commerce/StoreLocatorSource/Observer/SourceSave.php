<?php
 
 
namespace X247Commerce\StoreLocatorSource\Observer;
 
use \Psr\Log\LoggerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
 
 
class SourceSave implements ObserverInterface
{
    
    protected $logger;

    protected $sourceRepository;

    protected $sourceFactory;

    protected $locationFactory;

    protected $locationResource;
 
    public function __construct(
        LoggerInterface $logger,
        \Magento\Inventory\Model\SourceRepository $sourceRepository,
        \Magento\Inventory\Model\SourceFactory $sourceFactory,
        \Amasty\Storelocator\Model\LocationFactory $locationFactory,
        \Amasty\Storelocator\Model\ResourceModel\Location $locationResource
    ) {
        $this->logger = $logger;
        $this->locationFactory = $locationFactory;
        $this->sourceFactory = $sourceFactory;
        $this->sourceRepository = $sourceRepository;
        $this->locationResource =$locationResource;
    }
 
    public function execute(Observer $observer)
    {
        $data = $observer->getData('request')->getParams();
        $id = $data["general"]['source_code'];
        $idStore = $data["general"]['amlocator_store'];
        $soure = $this->sourceRepository->get($id);
        $collection = $this->sourceFactory->create()->getCollection();
        $storeCollection = $this->locationFactory->create()->getCollection();

        foreach ($collection as $value) {
            
            if ($value->getAmlocatorStore()==$idStore) {
                $value->setData("amlocator_store",'NULL')->save();
            }
        }

        foreach ($storeCollection as $value) {
            if ($value->getAmlocatorSource()==$id) {
                $value->setData("amlocator_source",'NULL')->save();
            }
        }

        $soure->setData("amlocator_store",$idStore)->save();
        $modelStore = $this->locationFactory->create();
        $store = $this->locationResource->load($modelStore,$idStore);
        $modelStore->setData("amlocator_source",$id)->save();
    }
}