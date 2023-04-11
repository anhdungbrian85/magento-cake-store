<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace X247Commerce\Checkout\Service;

use Magento\Framework\App\Http\Context as HttpContext;
use X247Commerce\Checkout\Api\StoreLocationContextInterface;
use Magento\Checkout\Model\Session as CheckoutSession;

class StoreLocationContext implements StoreLocationContextInterface
{

    protected HttpContext $httpContext;
    protected CheckoutSession $checkoutSession;

    public function __construct(
        HttpContext $httpContext,
        CheckoutSession $checkoutSession
    ) {
        $this->httpContext = $httpContext;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @inheritdoc
     */
    public function setStoreLocationId($storeLocationId)
    {
        $this->httpContext->setValue(StoreLocationContextInterface::STORE_LOCATION_ID, $storeLocationId, 0);
        $this->checkoutSession->setStoreLocationId($storeLocationId);
    }

    /**
     * @inheritdoc
     */
    public function getStoreLocationId()
    {
        return $this->httpContext->getValue(StoreLocationContextInterface::STORE_LOCATION_ID);
    }

    /**
     * @inheritdoc
     */
    public function setDeliveryType($deliveryType)
    {
        $this->httpContext->setValue(StoreLocationContextInterface::DELIVERY_TYPE, $deliveryType, 0);
        $this->checkoutSession->setDeliveryType($deliveryType);
    }

    /**
     * @inheritdoc
     */
    public function getDeliveryType()
    {
        return $this->httpContext->getValue(StoreLocationContextInterface::DELIVERY_TYPE);
    }

}
