<?php

namespace X247Commerce\StoreLocatorSource\Plugin;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Catalog\Model\Locator\LocatorInterface;

class ModifySourceItems
{
	protected $request;
	/**
	 * @var \Magento\Backend\Model\Auth\Session
	 */
	protected $_adminSession;
    protected $adminSourceFactory;
    private $locator;

	public function __construct(
		\Magento\Backend\Model\Auth\Session $adminSession,
        \X247Commerce\StoreLocatorSource\Model\AdminSourceFactory $adminSourceFactory,
        LocatorInterface $locator,
		HttpRequest $request
	) {
		$this->request = $request;
		$this->_adminSession = $adminSession;
        $this->adminSourceFactory = $adminSourceFactory;
        $this->locator = $locator;
    }

    public function afterModifyData(
    	\Magento\InventoryCatalogAdminUi\Ui\DataProvider\Product\Form\Modifier\SourceItems $subject,
    	$result
    ) {

	    	$roleData = $this->_adminSession->getUser()->getRole()->getData();
	    	$userData = $this->_adminSession->getUser()->getData();
	    	
	    	$product = $this->locator->getProduct();

	    	$roleId = (int) $roleData['role_id'];
	    	if ($roleId != 1) {
	    		$userSources = $this->getSourceCodeCollection($userData['user_id']);
	    		    	
	    		if (isset($result[$product->getId()]['sources']['assigned_sources'])) {
	    			$items = $result[$product->getId()]['sources']['assigned_sources'];

			    	$list = [];
			    	$unassign = [];
			    	foreach ($items as $item) {
			    		if (in_array($item["source_code"], $userSources)) {
			    			$list[] = $item;
			    		} else {
			    			$unassign[] = $item;
			    		}
			    	}

			    	$result[$product->getId()]['sources']['assigned_sources'] = $list;
			    	$result[$product->getId()]['sources']['unassigned_sources'] = $unassign;
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