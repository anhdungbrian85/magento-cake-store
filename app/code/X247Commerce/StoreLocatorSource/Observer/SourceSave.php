<?php
 
 
namespace X247Commerce\StoreLocatorSource\Observer;
 
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
 
class SourceSave implements ObserverInterface
{
    protected $sourceRepository;
    protected $locatorSourceResolver;
 
    public function __construct(
        \Magento\Inventory\Model\SourceRepository $sourceRepository,
        \X247Commerce\StoreLocatorSource\Model\ResourceModel\LocatorSourceResolver $locatorSourceResolver
    ) {
        $this->sourceRepository = $sourceRepository;
        $this->locatorSourceResolver = $locatorSourceResolver;
    }
 
    public function execute(Observer $observer)
    {
        $data = $observer->getData('request')->getParams();
        $storeIds = $data["general"]['amlocator_store'];
        $sourceCode = $data["general"]['source_code'];
        try {
            $this->locatorSourceResolver->reAssignAmLocatorStoresToSource($storeIds, $sourceCode);
        } catch (\Exception $e) {
            // echo $e->getMessage();die;
        }
        
    }
}