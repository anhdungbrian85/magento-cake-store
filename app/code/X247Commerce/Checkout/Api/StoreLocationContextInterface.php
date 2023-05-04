<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace X247Commerce\Checkout\Api;

interface StoreLocationContextInterface
{

    public const FREE_COLLECTION_TYPE_VALUE = 0;

    public const DELIVERY_TYPE_VALUE = 1;

    public const STORE_LOCATION_ID = 'store_location_id';

    public const DELIVERY_TYPE = 'delivery_type';

    public const CUSTOMER_POSTCODE = 'customer_postcode';

    public const GEOGRAPHIC_COORDINATE = 'geographic_coordinate';

    public const POPUP_CLOSED = 'popup_closed';

    /**
     * Unset all context/session.
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function unsetAllContexts();

    /**
     * Set Store localtion id to HttpContext and CheckoutSession.
     *
     * @param int $storeLocationId
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setStoreLocationId($storeLocationId);    
    /**
     * unset Store localtion id to HttpContext and CheckoutSession.
     *
     * @param int $storeLocationId
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function unSetStoreLocationId();

    /**
     * Get Store localtion id from HttpContext.
     *
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getStoreLocationId();
    /**
     * Set Customer Postcode to HttpContext and CheckoutSession.
     *
     * @param int $storeLocationId
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setCustomerPostcode($postcode);
    /**
     * UnSet Customer Postcode to HttpContext and CheckoutSession.
     *
     * @param int $storeLocationId
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function unSetCustomerPostcode();

    /**
     * Get Customer Postcode from HttpContext.
     *
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */

    public function getCustomerPostcode();

    /**
     * Set Customer Geographic Coordinate to HttpContext and CheckoutSession.
     *
     * @param int $storeLocationId
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setCustomerGeographicCoordinate($lat, $lng);

    /**
     * Get Customer Geographic Coordinate from HttpContext and CheckoutSession.
     *
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCustomerGeographicCoordinate();

    /**
     * Set Delivery Type to HttpContext.
     *
     * @param int $deliveryType
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setDeliveryType($deliveryType);

    /**
     * Get Delivery Type from HttpContext and CheckoutSession.
     *
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDeliveryType();
    /**
     * Unset Delivery Type in HttpContext and CheckoutSession.
     *
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function unSetDeliveryType();


     /**
     * Set popup_closed to context/session.
     * @param bool $isClosed
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setPopupClosed($isClosed);

     /**
     * Get popup_closed data.
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPopupClosed();

    /**
     * Unset popup_closed context/session.
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function unSetPopupClosed();
}
