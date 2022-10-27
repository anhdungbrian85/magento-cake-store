<?php

namespace X247Commerce\StoreLocatorSource\Plugin;

use Magento\Framework\App\RequestInterface;

class AssignSourceToOrder
{
	protected $request; 

	protected $sourceFactory;

	protected $order;

	public function __construct(
		RequestInterface $request,
		\Magento\Inventory\Model\SourceFactory $sourceFactory,
		\Magento\Sales\Model\OrderFactory $order
	) {
		$this->request = $request;
		$this->sourceFactory = $sourceFactory;
		$this->order = $order;
	}
	public function afterGetData(\Magento\InventoryShippingAdminUi\Ui\DataProvider\SourceSelectionDataProvider $subject, $result)
    {
    	$idOrder = $this->request->getParams();
    	$collection = $this->order->create()
    		->getCollection()
    		->addFieldToFilter('entity_id', ['eq' => $idOrder["order_id"]])
    		->getFirstItem();
    	$store = $collection->getStoreLocationId();

    	if (!empty($store)) {

    		$source = $this->sourceFactory->create()
    		->getCollection()
    		->addFieldToFilter('amlocator_store', ['eq' => $store])
    		->getFirstItem();
    		$codeSource = $source->getSourceCode();
    		$nameSource = $source->getName();
    		$i= 0;
            foreach ($result[$idOrder["order_id"]]['sourceCodes'] as  $value) {
            	if ($value['value'] != $codeSource) {
            		unset($result[$idOrder["order_id"]]['sourceCodes'][$i]);
            	}
            	$i++;
            }
    	}

    	return $result;
    }
}