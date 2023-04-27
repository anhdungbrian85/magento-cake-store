<?php

namespace X247Commerce\Checkout\Observer\Order;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use X247Commerce\Checkout\Api\StoreLocationContextInterface;

class ClearAfterOrder implements ObserverInterface
{
    protected $storeLocationContext;

    public function __construct(
        StoreLocationContextInterface $storeLocationContext
    ) {
        $this->storeLocationContext = $storeLocationContext;
    }

    public function execute(EventObserver $observer)
    {
        $this->storeLocationContext->unSetStoreLocationId();
        $this->storeLocationContext->unSetCustomerPostcode();
        $this->storeLocationContext->unSetDeliveryType();
        return $this;
    }
}