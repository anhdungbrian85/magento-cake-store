<?php

namespace X247Commerce\SmsTextManagement\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

use Psr\Log\LoggerInterface;

class InvoicePayObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    protected $helper;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectmanager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface   $objectmanager,
        \X247Commerce\SmsTextManagement\Helper\Data $helper,
        LoggerInterface                             $logger
    )
    {
        $this->helper = $helper;
        $this->_objectManager = $objectmanager;
        $this->_logger = $logger;
    }

    public function execute(EventObserver $observer)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/InvoicePayObserver.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $order = $observer->getInvoice()->getOrder();
        $logger->info('SEND SMS ORDER' . $order->getId());
        if ($this->helper->getApiConfig("active")) {
            try {
                $response = $this->helper->getSendNotifications($order);
                $logger->info(print_r($response, true));
            } catch (\Exception $e) {
                 $this->_logger->error('Cannot send the message for:'.$order->getId().'|'.$e->getMessage());
            }
        }
        return $this;
    }
}
