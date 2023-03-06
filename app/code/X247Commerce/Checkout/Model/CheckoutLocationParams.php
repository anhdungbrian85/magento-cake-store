<?php

namespace X247Commerce\Checkout\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use X247Commerce\Checkout\Api\StoreLocationContextInterface;

class CheckoutLocationParams
{
	protected StoreLocationContextInterface $storeLocationContext;

	public function __construct(
		StoreLocationContextInterface $storeLocationContext
	){
		$this->storeLocationContext = $storeLocationContext;
	}
    public function getConfig()
   	{
       return [
       		'storeLocationId' => $this->storeLocationContext->getStoreLocationId(), 
       		'deliveryType' => $this->storeLocationContext->getDeliveryType() 
       ];
   	}
}
