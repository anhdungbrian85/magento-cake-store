<?php
namespace X247Commerce\Checkout\Observer;

use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

class GetOrderDetails implements ObserverInterface
{
    protected $logger;

    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/OrderDetails.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        //$logger->info('text message');
        
        $order = $observer->getEvent()->getOrder();
        $OrderData = $order->getData();
        $payment = $order->getPayment();
        $method = $payment->getMethod();

        // Get order details
        // $orderId = $order->getIncrementId();
        // $orderStatus = $order->getStatus();
        // $payment = $order->getPayment();
        // $paymentMethod = $payment->getMethodInstance()->getTitle();

        // Log or handle the order details
        $logger->info(print_r("Order data : ". json_encode($OrderData), true));
        $logger->info(print_r("Payment Method : ". $method, true));
       // $this->logger->info("Order ID: $orderId, Status: $orderStatus, Payment Method: $paymentMethod");
    }
}
