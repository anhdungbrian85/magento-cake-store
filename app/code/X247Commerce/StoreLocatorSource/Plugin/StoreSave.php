<?php
namespace X247Commerce\StoreLocatorSource\Plugin;

class StoreSave
{
	protected $sourceRepository;

	protected $sourceFactory;

	protected $locationFactory;

	protected $locationResource;

	public function __construct(
		\Magento\Inventory\Model\SourceRepository $sourceRepository,
		\Magento\Inventory\Model\SourceFactory $sourceFactory,
		\Amasty\Storelocator\Model\LocationFactory $locationFactory,
		\Amasty\Storelocator\Model\ResourceModel\Location $locationResource
	) {
		$this->locationFactory = $locationFactory;
		$this->sourceFactory = $sourceFactory;
		$this->sourceRepository = $sourceRepository;
		$this->locationResource =$locationResource;
    }
    public function beforeExecute(\Amasty\Storelocator\Controller\Adminhtml\Location\Save $subject)
    {
		
		$data = $subject->getRequest()->getPostValue();
		$id = (int)$subject->getRequest()->getParam('id');
		$nameSource = $data["amlocator_source"];
		$collection = $this->sourceFactory->create()->getCollection();
		$storeCollection = $this->locationFactory->create()->getCollection();
		foreach ($collection as $value) {
			
			if ($value->getAmlocatorStore()==$id) {
				$value->setData("amlocator_store",'NULL')->save();

			}
		}	
		foreach ($storeCollection as $value) {
			if ($value->getAmlocatorSource()==$id) {
				$value->setData("amlocator_source",'NULL')->save();

			}
		}

		$soure = $this->sourceRepository->get($nameSource);
		$soure->setData("amlocator_store",$id)->save();
		$modelStore = $this->locationFactory->create();
		$store = $this->locationResource->load($modelStore,$id);
		$modelStore->setData("amlocator_source",$nameSource)->save();	
	}
}
