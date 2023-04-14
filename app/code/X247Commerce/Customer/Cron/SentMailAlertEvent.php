<?php

namespace X247Commerce\Customer\Cron;

use Magento\Store\Model\StoreManagerInterface;

class SentMailAlertEvent
{
    const EMAIL_SENDER = 'trans_email/ident_support/email';

    public function __construct(
        StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \X247Commerce\Customer\Model\EventFactory $eventFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_storeManager = $storeManager;
        $this->logger = $logger;
        $this->date = $date;
        $this->_inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
        $this->eventFactory = $eventFactory;
        $this->timezone = $timezone;
        $this->customerFactory = $customerFactory;
        $this->scopeConfig = $scopeConfig;
    }

    public function execute()
    {
        try {
            $currentDay = $this->date->gmtDate('Y-m-d');
            $events = $this->getEventList();
            $data = [];

            foreach ($events as $event) {
                if ($this->timezone->date(new \DateTime($event->getDate()))->format('Y-m-d') == $currentDay) {
                    $data[] = $event;
                }
            }
            if (count($data) > 0) {
                $this->sendMail($data);
            }
        } catch (\Exception $e) {

            $this->logger->info("DUMP:" . print_r($e, true));
            $this->logger->info("ERROR:" . $e->getMessage());
        }
    }

    public function getEventList()
    {
        return $this->eventFactory->create()->getCollection();
    }

    public function sendMail($events)
    {
        try {
            // Send Mail
            $this->_inlineTranslation->suspend();

            $dateNow = $this->date->gmtDate('Y-m-d');
            $storeId = $this->_storeManager->getStore()->getId();
            $customerFactory = $this->customerFactory->create();
            $sender = [
                'name' => 'Alert Reminder Notification Email',
                'email' => $this->scopeConfig->getValue(self::EMAIL_SENDER, \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            ];

            foreach ($events as $event) {

                $sentToEmail = $customerFactory->load($event->getCustomerId())->getEmail();
                $transport = $this->_transportBuilder
                    ->setTemplateIdentifier($this->scopeConfig->getValue('x247commerce_customer/event/email_template', \Magento\Store\Model\ScopeInterface::SCOPE_STORE))
                    ->setTemplateOptions(
                        [
                            'area' => 'frontend',
                            'store' => $storeId
                        ]
                    )
                    ->setTemplateVars([
                        'date' => $dateNow,
                        'their_name' => $event->getTheirName(),
                        'occasion' => $event->getOccasion()
                    ])
                    ->setFromByScope($sender)
                    ->addTo($sentToEmail)
                    ->getTransport();

                $transport->sendMessage();
            }

            $this->_inlineTranslation->resume();

        } catch (\Exception $e) {
            $this->_logger->debug($e->getMessage());
        }
    }
}
