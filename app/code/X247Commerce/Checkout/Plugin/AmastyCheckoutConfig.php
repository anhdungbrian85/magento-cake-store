<?php

namespace X247Commerce\Checkout\Plugin;

use X247Commerce\Checkout\Api\StoreLocationContextInterface;

class AmastyCheckoutConfig
{
    protected StoreLocationContextInterface $storeLocationContext;


    public function __construct(
        StoreLocationContextInterface $storeLocationContext
    ) {
        $this->storeLocationContext = $storeLocationContext;
    }

    public function afterGetDefaultShippingMethod(
    	\Amasty\CheckoutCore\Model\Config $subject, 
    	$result
    ) {
    	$deliveryTypeContext = $this->storeLocationContext->getDeliveryType();
    	if (null === $deliveryTypeContext) {
    		return $result;
    	}
        if ($deliveryTypeContext == 0) {
        	return \Amasty\StorePickupWithLocator\Model\Carrier\Shipping::SHIPPING_NAME;
        }
        if ($deliveryTypeContext == 1 || $deliveryTypeContext == 2) {
        	return 'cakeboxdelivery_cakeboxdelivery';
        }
        return $result;
    }
}
