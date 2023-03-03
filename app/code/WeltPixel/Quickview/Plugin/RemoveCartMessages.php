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
        if (!$this->helper->isAjaxCartEnabled()) {
            return $result;
        }

        if ($this->sessionManager->getData('wp_messages')) {
            $result['wp_messages'] = true;
            $this->sessionManager->unsetData('wp_messages');
        }

        if (isset($result['messages'])) {
            foreach ($result['messages'] as $id => $messageDetails) {
                $messageText = $messageDetails['text'];
                if (($messageDetails['type'] == 'success') && (!strlen($messageText))) {
                    unset($result['messages'][$id]);
                    $result['wp_messages'] = true;
                }
            }
        }

        return $result;

    }
}
