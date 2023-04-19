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
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/add_to_cart.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('Starting debug');
        $selectedLocationId = $this->locationContext->getStoreLocationId();
        $quote = $this->checkoutSession->getQuote();
        $logger->info('Selected Location Id: ' . $selectedLocationId );
        foreach($quote->getAllVisibleItems() as $item) {
            if (!$this->locatorSourceResolver->checkProductAvailableInStore($selectedLocationId, $item)) {
                $logger->info('Error sku: ' . $item->getSku() );
                throw new \Magento\Framework\Exception\LocalizedException(__('The product is out of stock on this location.'));
            }
        }
        $logger->info('Ending debug');
    }
}
