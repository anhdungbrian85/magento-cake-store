<?php

namespace X247Commerce\Checkout\Plugin\Checkout\Model;
use X247Commerce\Checkout\Api\StoreLocationContextInterface;
use Magento\Checkout\Model\Session as CheckoutSession;

class HideDeliveryShipping
{
    protected $checkoutSession;
    protected $storeLocationContext;

    public function __construct
    (
        CheckoutSession $checkoutSession,
        StoreLocationContextInterface $storeLocationContext
    )
    {
        $this->checkoutSession = $checkoutSession;
        $this->storeLocationContext = $storeLocationContext;
    }

    public function aroundCollectCarrierRates(
        \Magento\Shipping\Model\Shipping $subject,
        \Closure $proceed,
        $carrierCode,
        $request
    )
    {
        var_dump('$this->storeLocationContext->getDeliveryType()');
        var_dump($this->storeLocationContext->getDeliveryType());
        var_dump($carrierCode);
        
        if ($carrierCode == 'flatrate') {
            var_dump($carrierCode);
            return false;
        } 
        
            return $proceed($carrierCode, $request);
    }
}