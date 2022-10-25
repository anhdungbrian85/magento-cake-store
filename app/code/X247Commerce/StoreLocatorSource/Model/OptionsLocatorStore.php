<?php

namespace X247Commerce\StoreLocatorSource\Model;

class OptionsLocatorStore implements \Magento\Framework\Data\OptionSourceInterface
{

	protected $locationFactory;

	protected $collectionFactory;

	
	public function __construct(
		\Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $collectionFactory,
		\Amasty\Storelocator\Model\LocationFactory $locationFactory
	) {
		$this->locationFactory = $locationFactory;	
		$this->collectionFactory = $collectionFactory;
	}

	public function toOptionArray() {
		$options = [];
		$locationStore = $this->locationFactory->create()->getCollection();
					
		foreach ($locationStore as $store) {
			$options[] =
				[	'value' => $store->getId(),
					'label' =>$store->getName()
				];
		}
		if (count($options) > 0) {
            array_unshift(
                $options,
                ['title' => '', 'value' => '', 'label' => __('Please Select Locator Store')]
            );
        }
        return $options;
	}
}