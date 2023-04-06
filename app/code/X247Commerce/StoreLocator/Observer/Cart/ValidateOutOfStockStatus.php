<?php

namespace X247Commerce\StoreLocator\Observer\Cart;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use X247Commerce\StoreLocatorSource\Model\ResourceModel\LocatorSourceResolver;

class ValidateOutOfStockStatus implements ObserverInterface
{
    protected $checkoutSession;

    protected $locationContext;

    protected $locatorSourceResolver;

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \X247Commerce\Checkout\Api\StoreLocationContextInterface $locationContext,
        LocatorSourceResolver $locatorSourceResolver
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->locationContext = $locationContext;
        $this->locatorSourceResolver = $locatorSourceResolver;
    }

    /**
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        $selectedLocationId = $this->locationContext->getStoreLocationId();
        $quote = $this->checkoutSession->getQuote();
        foreach($quote->getAllVisibleItems() as $item) {
            if (!$this->locatorSourceResolver->checkProductAvailableInStore($selectedLocationId, $item)) {
                throw new \Magento\Framework\Exception\LocalizedException(__('The product is out of stock on this location.'));
            }
        }
    }
}
