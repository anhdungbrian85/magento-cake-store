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
        )
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/confirmation_popup.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('afterGetSectionData start');
        if (!$this->helper->isAjaxCartEnabled()) {
            $logger->info('afterGetSectionData isAjaxCartEnabled disable');
            return $result;
        }

        if ($this->sessionManager->getData('wp_messages')) {
            $logger->info('afterGetSectionData Session Manager wp_messages isset');
            $result['wp_messages'] = true;
            $this->sessionManager->unsetData('wp_messages');
        }

        if (isset($result['messages'])) {
            $logger->info('afterGetSectionData Check messages isset');
            foreach ($result['messages'] as $id => $messageDetails) {
                $messageText = $messageDetails['text'];
                if (($messageDetails['type'] == 'success') && (!strlen($messageText))) {
                    $logger->info('afterGetSectionData Check messages success');
                    unset($result['messages'][$id]);
                    $result['wp_messages'] = true;
                }
            }
        }
        $logger->info('afterGetSectionData end');
        return $result;

    }
}
