<?php

namespace X247Commerce\CancelOrderEmail\Observer;

use Magento\Framework\Event\ObserverInterface;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\Mail\Template\TransportBuilder;
use \Magento\Framework\Translate\Inline\StateInterface;
use Psr\Log\LoggerInterface;
use Ulmod\Productinquiry\Model\ConfigData;
use Magento\Framework\Escaper;


class CancelOrder implements ObserverInterface
{
        /**
     * @var ConfigData
     */
    public $configData;
    protected $_escaper;
    public function __construct(
        StoreManagerInterface $storeManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        TransportBuilder $transportBuilder,
        LoggerInterface $logLoggerInterface,
        StateInterface $inlineTranslation,
        ConfigData $configData,
        Escaper $escaper

    ) {
        $this->storeManager = $storeManager;
        $this->messageManager = $messageManager;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->logLoggerInterface = $logLoggerInterface;
        $this->configData = $configData;
        $this->_escaper = $escaper;
        
    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $order = $observer->getEvent()->getOrder();
            $custFirstname = ''; # Set customer name from order
            $custEmail = ''; # Set customer email from order
            $template_sub = ''; # Set email subject
            $autoReplyEmailSender = [
                'name' => $this->_escaper->escapeHtml($this->configData->getAutoReplySenderName()),
                'email' => $this->_escaper->escapeHtml($this->configData->getAutoReplySenderEmail()),
            ];
            $template_content = ''; # Set email content
            $templateId = 'cancel_order_template';
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
                    'template_subject' => $template_sub,
                    'customername' => $custFirstname,
                    'email_content' => $template_content,
                ])
                ->setFrom($autoReplyEmailSender)
                ->addTo(array($sendTo))
                ->getTransport();
            $transport->sendMessage();
            // var_dump($transport); die();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->logLoggerInterface->debug($e->getMessage());
            exit;
        }
    }
}