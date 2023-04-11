<?php

namespace X247Commerce\StoreLocatorSource\Plugin;

class StoreSave
{
	protected $sourceRepository;

	protected $sourceFactory;

	protected $locationFactory;

	protected $locationResource;

	protected $locatorSourceResolver;

	public function __construct(
		\Magento\Inventory\Model\SourceRepository $sourceRepository,
		\Magento\Inventory\Model\SourceFactory $sourceFactory,
		\Amasty\Storelocator\Model\LocationFactory $locationFactory,
		\Amasty\Storelocator\Model\ResourceModel\Location $locationResource,
		\X247Commerce\StoreLocatorSource\Model\ResourceModel\LocatorSourceResolver $locatorSourceResolver
	) {
		$this->locationFactory = $locationFactory;
		$this->sourceFactory = $sourceFactory;
		$this->sourceRepository = $sourceRepository;
		$this->locationResource = $locationResource;
		$this->locatorSourceResolver = $locatorSourceResolver;
    }
    public function beforeExecute(\Amasty\Storelocator\Controller\Adminhtml\Location\Save $subject)
    {
		
		$data = $subject->getRequest()->getPostValue();
		// var_dump($data);die();
		$id = (int)$subject->getRequest()->getParam('id');

		$nameSource = isset($data["amlocator_source"]) ? $data["amlocator_source"] : false;
		if ($nameSource) {
			$soure = $this->sourceRepository->get($nameSource);
			$idSource = $soure->getId();
			$collection = $this->sourceFactory->create()->getCollection();
			$storeCollection = $this->locationFactory->create()->getCollection();
			foreach ($collection as $value) {
				
				if ($value->getAmlocatorStore()==$id) {
					$value->setData("amlocator_store",'NULL')->save();

				}
			}	
			foreach ($storeCollection as $value) {
				if ($value->getAmlocatorSource()==$idSource) {
					$value->setData("amlocator_source",'NULL')->save();

				}
			}

			$soure->setData("amlocator_store",$id)->save();
			$modelStore = $this->locationFactory->create();
			$store = $this->locationResource->load($modelStore,$id);
			$modelStore->setData("amlocator_source",$nameSource)->save();
		}
		$oldParentLocationId = $this->locatorSourceResolver->getAsdaLocationParentLocation($data["id"]);
		$newParentLocationId = isset($data["amlocator_store"]) ? $data["amlocator_store"] : '';
		
		if ($oldParentLocationId != $newParentLocationId) {
			$this->locatorSourceResolver->unAssignAsdaAmLocatorStoreToParent($oldParentLocationId, $data["id"]);
			if (!empty($newParentLocationId)) {
				$this->locatorSourceResolver->assignAsdaAmLocatorStoreToParent($newParentLocationId, $data["id"]);
			}
		}		
	}
}
