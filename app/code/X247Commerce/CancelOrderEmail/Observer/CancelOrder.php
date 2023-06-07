<?php

namespace X247Commerce\CancelOrderEmail\Observer;

use Magento\Framework\Event\ObserverInterface;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\Mail\Template\TransportBuilder;
use \Magento\Framework\Translate\Inline\StateInterface;
use Psr\Log\LoggerInterface;
use X247Commerce\CancelOrderEmail\Model\ConfigData;
use Magento\Framework\Escaper;
use X247Commerce\CancelOrderEmail\Helper\Data;

class CancelOrder implements ObserverInterface
{
    /**
     * @var ConfigData
     */
    public $configData;
    protected $_escaper;
    protected $dataHelper;
    public function __construct(
        StoreManagerInterface $storeManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        TransportBuilder $transportBuilder,
        LoggerInterface $logLoggerInterface,
        StateInterface $inlineTranslation,
        ConfigData $configData,
        Escaper $escaper,
        Data $dataHelper
    ) {
        $this->storeManager = $storeManager;
        $this->messageManager = $messageManager;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->logLoggerInterface = $logLoggerInterface;
        $this->configData = $configData;
        $this->_escaper = $escaper;
        $this->dataHelper = $dataHelper;
    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            if ($this->dataHelper->getConfigValue('sales_email/order_cancel/enabled') == '1') {
                $order = $observer->getEvent()->getOrder();
                $custEmail = $observer->getEvent()->getOrder()->getCustomer_email();
                $autoReplyEmailSender = [
                    'name' => $this->_escaper->escapeHtml($this->configData->getAutoReplySenderName()),
                    'email' => $this->_escaper->escapeHtml($this->configData->getAutoReplySenderEmail()),
                ];
                $templateId = $this->dataHelper->getConfigValue('sales_email/order_cancel/template');
                $this->inlineTranslation->suspend();
                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                $sendTo = $custEmail;
                $transport = $this->transportBuilder
                    ->setTemplateIdentifier($templateId)
                    ->setTemplateOptions(
                        [
                            'area' => 'frontend',
                            'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                        ]
                    )
                    ->setTemplateVars([
                        'order_data' => [
                            'customer_name' => $observer->getEvent()->getOrder()->getCustomer_firstname(),
                            'increment_id' => $order->getIncrement_id(),
                        ]
                    ])
                    ->setFrom($autoReplyEmailSender)
                    ->addTo(array($sendTo))
                    ->getTransport();
                $transport->sendMessage();
                $this->inlineTranslation->resume();
            }
        } catch (\Exception $e) {
            $this->logLoggerInterface->debug($e->getMessage());
            exit;
        }
    }
}