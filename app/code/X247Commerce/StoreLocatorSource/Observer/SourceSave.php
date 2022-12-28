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
        $idStore = $data["general"]['amlocator_store'];
        $sourceCode = $data["general"]['source_code'];
        $source = $this->sourceRepository->get($sourceCode);
        $storeOfSource = $this->locatorSourceResolver->getAmLocatorBySource($sourceCode);
        if (is_array($idStore)) {
            $newAssignStore = array_diff($idStore, $storeOfSource);
            $unAssignStore = array_diff($storeOfSource, $idStore);
            
            if ($newAssignStore) {
                foreach ($newAssignStore as $id) {
                    $this->locatorSourceResolver->assignAmLocatorStoreToSource($id, $sourceCode);
                }
            }

            if ($unAssignStore) {
                foreach ($unAssignStore as $id) {
                    $this->locatorSourceResolver->unAssignAmLocatorStoreWithSource($id, $sourceCode);
                }
            }
           $source->setData("amlocator_store",implode(",",$idStore))->save();
        }
    }
}