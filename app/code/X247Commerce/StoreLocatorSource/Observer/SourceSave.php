<?php
 
 
namespace X247Commerce\StoreLocatorSource\Observer;
 
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
 
 
class SourceSave implements ObserverInterface
{
    
    protected $sourceRepository;
 
    public function __construct(
        \Magento\Inventory\Model\SourceRepository $sourceRepository
    ) {
        $this->sourceRepository = $sourceRepository;
    }
 
    public function execute(Observer $observer)
    {
        $data = $observer->getData('request')->getParams();
        $idStore = $data["general"]['amlocator_store'];
        $id = $data["general"]['source_code'];
        $soure = $this->sourceRepository->get($id);
        $soure->setData("amlocator_store",$idStore)->save();
    }
}