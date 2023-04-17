<?php

namespace X247Commerce\Customer\Cron;

use Magento\Store\Model\StoreManagerInterface;

class SentMailAlertEvent
{
    const EMAIL_SENDER = 'trans_email/ident_support/email';

    protected $_storeManager;
    protected $logger;
    protected $_inlineTranslation;
    protected $_transportBuilder;
    protected $eventFactory;
    protected $timezone;
    protected $customerFactory;
    protected $scopeConfig;

    public function __construct(
        StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \X247Commerce\Customer\Model\EventFactory $eventFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_storeManager = $storeManager;
        $this->logger = $logger;
        $this->_inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
        $this->eventFactory = $eventFactory;
        $this->timezone = $timezone;
        $this->customerFactory = $customerFactory;
        $this->scopeConfig = $scopeConfig;
    }

    public function execute($count = null, $eventId = null, $customerId = null)
    {
        try {
            $runCronDay = date('Y-m-d', strtotime('+30 days'));
            $events = $this->getEventList();

            if ($count == null) {
                foreach ($events as $event) {
                    if ($this->timezone->date(new \DateTime($event->getDate()))->format('Y-m-d') == $runCronDay) {
                        $this->sendMail($event);
                    }
                }
            } else {
                $event = $this->getEvent($eventId, $customerId);
                $this->sendMail($event);
            }
        } catch (\Exception $e) {

            $this->logger->info("DUMP:" . print_r($e, true));
            $this->logger->info("ERROR:" . $e->getMessage());
        }
    }

    protected function getEvent($eventId, $customerId)
    {
        $eventCollectionFactory = $this->eventFactory->create()->getCollection();

        if ($eventId != '') {
            return $eventCollectionFactory->addFieldToFilter('id', ['eq' => $eventId]);
        }

        return $eventCollectionFactory
                ->addFieldToFilter('customer_id', ['eq' => $customerId])
                ->setOrder('id', 'DESC')
                ->getFirstItem();
    }

    protected function getEventList()
    {
        return $this->eventFactory->create()->getCollection();
    }

    protected function sendMail($event)
    {
        try {
            $this->_inlineTranslation->suspend();

            $storeId = $this->_storeManager->getStore()->getId();
            $customerFactory = $this->customerFactory->create();
            $emailTemplateIdentifier = $this->scopeConfig->getValue('x247commerce_customer/event/email_template', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $sender = [
                'name' => 'Alert Reminder Notification Email',
                'email' => $this->scopeConfig->getValue(self::EMAIL_SENDER, \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            ];

            $eventDate = $this->timezone->date(new \DateTime(empty($event->getData()[0]) ? $event->getDate() : $event->getData()[0]['date']))->format('Y-m-d');
            $sentToEmail = $customerFactory->load(empty($event->getData()[0]) ? $event->getCustomerId() : $event->getData()[0]['customer_id'])->getEmail();
            $transport = $this->_transportBuilder
                ->setTemplateIdentifier($emailTemplateIdentifier)
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => $storeId
                    ]
                )
                ->setTemplateVars([
                    'event_date' => $eventDate,
                    'their_name' => empty($event->getData()[0]) ? $event->getTheirName() : $event->getData()[0]['their_name'],
                    'occasion' => empty($event->getData()[0]) ? $event->getOccasion() : $event->getData()[0]['occasion']

                ])
                ->setFromByScope($sender)
                ->addTo($sentToEmail)
                ->getTransport();

            $transport->sendMessage();

            $this->_inlineTranslation->resume();

        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }
    }
}
