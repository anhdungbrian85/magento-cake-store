<?php

namespace X247Commerce\Checkout\Observer\Order;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class UpdateStoreLocationSelected implements ObserverInterface
{

    protected $logger;

    public function __construct(
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    public function execute(EventObserver $observer)
    {
        try {
            $event = $observer->getEvent();
            $quote = $event->getQuote();
            $order = $event->getOrder();
            if ($quote->getData('store_location_id')) {
                $order->setData('store_location_id', $quote->getData('store_location_id'));
            }
        } catch (\Exception $e) {
            $this->logger->addLog('Save store location not working: ' . $e->getMessage());
        }
        return;
    }
}
