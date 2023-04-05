<?php

namespace X247Commerce\StoreLocator\Observer\Cart;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use X247Commerce\StoreLocatorSource\Model\ResourceModel\LocatorSourceResolver;

class ValidateOutOfStockStatus implements ObserverInterface
{

    protected $locationContext;

    protected $locatorSourceResolver;

    public function __construct(
        \X247Commerce\Checkout\Api\StoreLocationContextInterface $locationContext,
        LocatorSourceResolver $locatorSourceResolver
    ) {
        $this->locationContext = $locationContext;
        $this->locatorSourceResolver = $locatorSourceResolver;
    }

    /**
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        $product = $observer->getProduct();
        $selectedLocationId = $this->locationContext->getStoreLocationId();
        if (!$this->locatorSourceResolver->checkProductAvailableInStore($selectedLocationId, $product)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('The product is out of stock on this location.'));
        }
    }
}
