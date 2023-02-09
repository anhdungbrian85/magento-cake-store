<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace X247Commerce\Checkout\Service;

use Magento\Framework\App\Http\Context as HttpContext;
use X247Commerce\Checkout\Api\StoreLocationContextInterface;

class StoreLocationContext implements StoreLocationContextInterface
{

    protected HttpContext $httpContext;

    public function __construct(
        HttpContext $httpContext
    ) {
        $this->httpContext = $httpContext;
    }

    /**
     * @inheritdoc
     */
    public function setStoreLocationId($storeLocationId)
    {
        $this->httpContext->setValue(StoreLocationContextInterface::STORE_LOCATION_ID, $storeLocationId, 0);
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
    }

    /**
     * @inheritdoc
     */
    public function getDeliveryType()
    {
        return $this->httpContext->getValue(StoreLocationContextInterface::DELIVERY_TYPE);
    }

}
