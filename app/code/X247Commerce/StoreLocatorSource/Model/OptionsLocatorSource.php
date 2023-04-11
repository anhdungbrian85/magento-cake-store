<?php

namespace X247Commerce\StoreLocatorSource\Model;

class OptionsLocatorSource implements \Magento\Framework\Data\OptionSourceInterface
{

	protected $source;

	protected $collectionFactory;
	
	public function __construct(
		\Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $collectionFactory,
		\Magento\Inventory\Model\SourceFactory $source
	) {
		$this->collectionFactory = $collectionFactory;
		$this->source = $source;
		
	}

	public function toOptionArray() {
		$options = [];
		$locationStore = $this->source->create()->getCollection();
					
		foreach ($locationStore as $source) {
			$options[] =
				[	'value' => $source->getId(),
					'label' =>$source->getName()
				];
		}
		if (count($options) > 0) {
            array_unshift(
                $options,
                ['title' => '', 'value' => '', 'label' => __('Please Select Locator Source')]
            );
        }
        return $options;
	}
}