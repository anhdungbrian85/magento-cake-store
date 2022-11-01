<?php

namespace X247Commerce\StoreLocatorSource\Plugin;

use X247Commerce\StoreLocatorSource\Model\ResourceModel\LocatorSourceResolver;
use Magento\Framework\App\Request\Http as HttpRequest;

class AssignAmStoreLocatorData
{
	protected $locatorSourceResolver;
	protected $request;
	/**
	 * @var \Magento\Backend\Model\Auth\Session
	 */
	protected $_adminSession;
    protected $adminSourceFactory;

	public function __construct(
		LocatorSourceResolver $locatorSourceResolver,
		\Magento\Backend\Model\Auth\Session $adminSession,
        \X247Commerce\StoreLocatorSource\Model\AdminSourceFactory $adminSourceFactory,
		HttpRequest $request
	) {
		$this->locatorSourceResolver = $locatorSourceResolver;
		$this->request = $request;
		$this->_adminSession = $adminSession;
        $this->adminSourceFactory = $adminSourceFactory;
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
    	} else {
	    	$roleData = $this->_adminSession->getUser()->getRole()->getData();
	    	$userData = $this->_adminSession->getUser()->getData();
	    	$roleId = (int) $roleData['role_id'];
	    	if ($roleId != 1) {
	    		$userSources = $this->getSourceCodeCollection($userData['user_id']);
	    	
	    		if (isset($result['items'])) {
	    			$items = $result['items'];
	    		}

		    	$list = [];
		    	foreach ($items as $item) {
		    		if (in_array($item["source_code"], $userSources)) {
		    			$list[] = $item;
		    		}
		    	}

		    	$result = [];
		    	$result['items'] = $list;
		    	$result['totalRecords'] = count($list);
	    	}
    	}
    	
		return $result;
	}

    public function getSourceCodeCollection($userId)
    {

        $collection = $this->adminSourceFactory->create()->getCollection()->addFieldToFilter('user_id', ['eq' => $userId]);
        $data = [];
        if ($collection) {
            foreach ($collection as $item) {
                $data[] = $item->getSourceCode();
            }
        }
     
        return $data;     
    }
}
