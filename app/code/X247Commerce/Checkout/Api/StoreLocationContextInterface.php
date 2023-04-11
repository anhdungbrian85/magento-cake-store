<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace X247Commerce\Checkout\Api;

interface StoreLocationContextInterface
{

    public const STORE_LOCATION_ID = 'store_location_id';

    public const DELIVERY_TYPE = 'delivery_type';

    /**
     * Set Store localtion id to HttpContext.
     *
     * @param int $storeLocationId
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setStoreLocationId($storeLocationId);

    /**
     * Get Store localtion id from HttpContext.
     *
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getStoreLocationId();

    /**
     * Set Delivery Type to HttpContext.
     *
     * @param int $deliveryType
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setDeliveryType($deliveryType);

    /**
     * Get Delivery Type from HttpContext.
     *
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDeliveryType();
}
