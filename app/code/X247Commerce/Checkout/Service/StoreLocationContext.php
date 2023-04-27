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

    public function unSetStoreLocationId()
    {
        $this->httpContext->unsValue(StoreLocationContextInterface::STORE_LOCATION_ID);
        $this->checkoutSession->unsStoreLocationId();
    }

    /**
     * @inheritdoc
     */
    public function getStoreLocationId()
    {
        return $this->httpContext->getValue(StoreLocationContextInterface::STORE_LOCATION_ID);
    }

    public function setCustomerPostcode($postcode)
    {
        $this->httpContext->setValue(StoreLocationContextInterface::CUSTOMER_POSTCODE, $postcode, 0);
        $this->checkoutSession->setCustomerPostcode($postcode);
    }

    public function unSetCustomerPostcode()
    {
        $this->httpContext->unsValue(StoreLocationContextInterface::CUSTOMER_POSTCODE);
        $this->checkoutSession->unsCustomerPostcode();
    }

    public function getCustomerPostcode()
    {
        return $this->httpContext->getValue(StoreLocationContextInterface::CUSTOMER_POSTCODE);
    }

    public function setCustomerGeographicCoordinate($lat, $lng)
    {
        $geographicCoordinate = ['lat' => $lat, 'lng' => $lng];

        $this->httpContext->setValue(StoreLocationContextInterface::GEOGRAPHIC_COORDINATE, json_encode($geographicCoordinate), 0);
        $this->checkoutSession->setGeographicCoordinate(json_encode($geographicCoordinate));
    }

    public function getCustomerGeographicCoordinate()
    {
        return $this->httpContext->getValue(StoreLocationContextInterface::GEOGRAPHIC_COORDINATE);
    }

    public function setDeliveryType($deliveryType)
    {
        $this->httpContext->setValue(StoreLocationContextInterface::DELIVERY_TYPE, $deliveryType, 0);
        $this->checkoutSession->setDeliveryType($deliveryType);
    }

    public function unSetDeliveryType()
    {
        $this->httpContext->unsValue(StoreLocationContextInterface::DELIVERY_TYPE);
        $this->checkoutSession->unsDeliveryType();        
    }

    /**
     * @inheritdoc
     */
    public function getDeliveryType()
    {
        return $this->httpContext->getValue(StoreLocationContextInterface::DELIVERY_TYPE);
    }

}
