<?php

namespace X247Commerce\StoreLocatorSource\Plugin;

use X247Commerce\StoreLocatorSource\Model\ResourceModel\LocatorSourceResolver;
use Magento\Framework\App\Request\Http as HttpRequest;

class AssignAmStoreLocatorData
{
	protected $locatorSourceResolver;
	protected $request;

	public function __construct(
		LocatorSourceResolver $locatorSourceResolver,
		HttpRequest $request
	) {
		$this->locatorSourceResolver = $locatorSourceResolver;
		$this->request = $request;
    }

    public function afterGetData(
    	\Magento\InventoryAdminUi\Ui\DataProvider\SourceDataProvider $subject,
    	$result
    ) {
    	$action = $this->request->getFullActionName();
    	if ($action == 'inventory_source_edit') {
    		// Only when edit source 
    		foreach($result as $sourceCode => &$sourceData) {
	    		if (!empty($sourceCode)) {
		    		$sourceData['general']['amlocator_store'] = $this->locatorSourceResolver->getAmLocatorBySource($sourceCode);
		    	}
	    	}
    	}
    	
		return $result;
	}
}
