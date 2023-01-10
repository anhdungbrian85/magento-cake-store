<?php

namespace X247Commerce\StoreLocatorSource\Plugin;

use X247Commerce\StoreLocatorSource\Model\ResourceModel\LocatorSourceResolver;
use Magento\Framework\App\Request\Http as HttpRequest;

class AssignAsdaStoreParentLocator
{
	protected $locatorSourceResolver;
	protected $request;
	/**
	 * @var \Magento\Backend\Model\Auth\Session
	 */
	protected $_adminSession;

	public function __construct(
		LocatorSourceResolver $locatorSourceResolver,
		\Magento\Backend\Model\Auth\Session $adminSession,
		HttpRequest $request
	) {
		$this->locatorSourceResolver = $locatorSourceResolver;
		$this->request = $request;
		$this->_adminSession = $adminSession;
    }

    public function afterGetData(
    	\Amasty\Storelocator\Ui\DataProvider\Form\LocationDataProvider $subject,
    	$result
    ) {

    	$action = $this->request->getFullActionName();
    	$currentLocationId = $result["items"][0]["id"];
    	
    	if ($action == 'amasty_storelocator_location_edit') {
    		// Only when edit location
    		if (isset($result["items"][0])) {
    			$result["items"][0]['amlocator_store'] = $this->getAsdaLocationParentLocation($currentLocationId);
    		}
		    if (isset($result[1])) {
		    	$result[1]['amlocator_store'] = $this->getAsdaLocationParentLocation($currentLocationId);
		    }		    
	    }
	    
		return $result;
	}

    public function getChildAsdaLocationCollection($parentLocationId)
    {
    	$data = $this->locatorSourceResolver->getChildAsdaLocationCollection($parentLocationId);
    	
        return $data;     
    }
    public function getAsdaLocationParentLocation($asdaLocationId)
    {
    	$data = $this->locatorSourceResolver->getAsdaLocationParentLocation($asdaLocationId);

        return $data;     
    }
}
