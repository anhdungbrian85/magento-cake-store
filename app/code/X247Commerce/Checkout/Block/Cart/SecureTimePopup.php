<?php

namespace X247Commerce\Checkout\Block\Cart;

use Magento\Checkout\Model\CompositeConfigProvider;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Serialize\Serializer\JsonHexTag;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\ObjectManager;

class SecureTimePopup extends \Magento\Checkout\Block\Cart\AbstractCart
{

    protected $configProvider;

    protected $layoutProcessors;

    protected $serializer;

    protected $jsonHexTagSerializer;

    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession,
        CompositeConfigProvider $configProvider,
        array $layoutProcessors = [],
        array $data = [],
        Json $serializer = null,
        JsonHexTag $jsonHexTagSerializer = null
    ) {
        $this->configProvider = $configProvider;
        $this->layoutProcessors = $layoutProcessors;
        parent::__construct($context, $customerSession, $checkoutSession, $data);
        $this->_isScopePrivate = true;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
        $this->jsonHexTagSerializer = $jsonHexTagSerializer ?: ObjectManager::getInstance()->get(JsonHexTag::class);
    }

    /**
     * Retrieve checkout configuration
     *
     * @return array
     * @codeCoverageIgnore
     */
    public function getCheckoutConfig()
    {
        return $this->configProvider->getConfig();
    }

    /**
     * Retrieve serialized JS layout configuration ready to use in template
     *
     * @return string
     */
    public function getJsLayout()
    {
        foreach ($this->layoutProcessors as $processor) {
            $this->jsLayout = $processor->process($this->jsLayout);
        }

        return $this->jsonHexTagSerializer->serialize($this->jsLayout);
    }

    /**
     * Get base url for block.
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    /**
     * Get Serialized Checkout Config
     *
     * @return bool|string
     * @since 100.2.0
     */
    public function getSerializedCheckoutConfig()
    {
        return $this->jsonHexTagSerializer->serialize($this->getCheckoutConfig());
    }
}
