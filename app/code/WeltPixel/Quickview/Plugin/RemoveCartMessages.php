<?php

namespace WeltPixel\Quickview\Plugin;

use Magento\Framework\Session\SessionManagerInterface;

class RemoveCartMessages
{
    /**
     * @var \WeltPixel\Quickview\Helper\Data $helper
     */
    protected $helper;

    /**
     * @var SessionManagerInterface
     */
    protected $sessionManager;

    /**
     * @param \WeltPixel\Quickview\Helper\Data $helper
     * @param SessionManagerInterface $sessionManager
     */
    public function __construct(
        \WeltPixel\Quickview\Helper\Data $helper,
        SessionManagerInterface $sessionManager
        ) {
        $this->helper = $helper;
        $this->sessionManager = $sessionManager;
    }

    /**
     * @param \Magento\Theme\CustomerData\Messages $subject
     * @param $result
     * @return mixed
     */
    public function afterGetSectionData(
        \Magento\Theme\CustomerData\Messages $subject,
        $result
    ) {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/confirmation_popup.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('afterGetSectionData start');
        if (!$this->helper->isAjaxCartEnabled()) {
            $logger->info('afterGetSectionData isAjaxCartEnabled disable');
            return $result;
        }
        $logger->info('afterGetSectionData Start Session Manager wp_messages isset');
        if ($this->sessionManager->getData('wp_messages')) {
            $result['wp_messages'] = true;
            $result['wp_messages_loaded'] = true;
            $this->sessionManager->unsetData('wp_messages');
            $logger->info('afterGetSectionData Session Manager wp_messages isset');
        }
        $logger->info('afterGetSectionData Start Check messages isset');
        if (isset($result['messages'])) {
            $logger->info('afterGetSectionData Check messages isset');
            $logger->info('afterGetSectionData result messages'.print_r($result['messages'], true));
            foreach ($result['messages'] as $id => $messageDetails) {
                $messageText = $messageDetails['text'];
                $logger->info('afterGetSectionData messageText:', $messageText);
                $logger->info('afterGetSectionData messageDetails:' . $messageDetails['type']);
                if (($messageDetails['type'] == 'success') && (!strlen($messageText))) {
                    unset($result['messages'][$id]);
                    $result['wp_messages'] = true;
                    $result['wp_messages_loaded'] = true;
                    $logger->info('afterGetSectionData Check messages success');
                }
            }
        }
        $logger->info('afterGetSectionData end');
        return $result;

    }
}
