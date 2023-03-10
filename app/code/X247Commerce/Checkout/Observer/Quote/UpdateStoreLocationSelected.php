<?php

namespace X247Commerce\Checkout\Observer\Quote;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use X247Commerce\Checkout\Api\StoreLocationContextInterface;

class UpdateStoreLocationSelected implements ObserverInterface
{
    protected $checkoutSession;

    protected $storeLocationContext;

    public function __construct(
        CheckoutSession $checkoutSession,
        StoreLocationContextInterface $storeLocationContext
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->storeLocationContext = $storeLocationContext;
    }

    public function execute(EventObserver $observer)
    {
        if ($this->storeLocationContext->getStoreLocationId()) {
            $quote = $this->checkoutSession->getQuote()->setData('store_location_id', $this->storeLocationContext->getStoreLocationId())->save();
        }
        return;
    }
}