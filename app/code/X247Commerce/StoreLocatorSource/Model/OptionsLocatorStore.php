<?php

namespace X247Commerce\StoreLocatorSource\Model;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;
use Amasty\Storelocator\Model\LocationFactory;
use Magento\Framework\App\Request\Http as HttpRequest;
class OptionsLocatorStore implements \Magento\Framework\Data\OptionSourceInterface
{

	protected CollectionFactory $collectionFactory;
	protected LocationFactory $locationFactory;
	protected HttpRequest $request;
	
	public function __construct(
		CollectionFactory $collectionFactory,
		LocationFactory $locationFactory,
		HttpRequest $request

	) {
		$this->locationFactory = $locationFactory;	
		$this->collectionFactory = $collectionFactory;
		$this->request = $request;
	}

	public function toOptionArray() {

		$options = [];
		$locationStore = $this->locationFactory->create()->getCollection()->setOrder('name','ASC');;
		$currentStoreId = $this->request->getParam('id');	

		foreach ($locationStore as $store) {
			if ($currentStoreId == $store->getId()) {
				continue;
			}
			$options[] =
				[	'value' => $store->getId(),
					'label' => $store->getName()
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