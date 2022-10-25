<?php

namespace X247Commerce\StoreLocatorSource\Plugin;

use X247Commerce\StoreLocatorSource\Model\ResourceModel\LocatorSourceResolver;

class AssignAmStoreLocatorData
{
	protected $locatorSourceResolver;

	public function __construct(
		LocatorSourceResolver $locatorSourceResolver
	) {
		$this->locatorSourceResolver = $locatorSourceResolver;
    }

    public function afterGetData(
    	\Magento\InventoryAdminUi\Ui\DataProvider\SourceDataProvider $subject,
    	$result
    ) {
    	
    	foreach($result as $sourceCode => &$sourceData) {
    		if (!empty($sourceCode)) {
	    		$sourceData['general']['amlocator_store'] = $this->locatorSourceResolver->getAmLocatorBySource($sourceCode);
	    	}
    	}
		return $result;
	}
}
