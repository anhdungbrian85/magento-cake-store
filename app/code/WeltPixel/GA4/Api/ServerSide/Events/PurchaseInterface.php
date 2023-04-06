<?php

namespace WeltPixel\GA4\Api\ServerSide\Events;

interface PurchaseInterface
{
    /**
     * @param $clientId
     * @return PurchaseInterface
     */
    function setClientId($clientId);

    /**
     * @param $sessionId
     * @return PurchaseInterface
     */
    function setSessionId($sessionId);

    /**
     * @param $timestamp
     * @return PurchaseInterface
     */
    function setTimestamp($timestamp);

    /**
     * @param $userId
     * @return PurchaseInterface
     */
    function setUserId($userId);

    /**
     * @param $currency
     * @return PurchaseInterface
     */
    function setCurrency($currency);

    /**
     * @param $transactionId
     * @return PurchaseInterface
     */
    function setTransactionId($transactionId);

    /**
     * @param $value
     * @return PurchaseInterface
     */
    function setValue($value);

    /**
     * @param $affiliation
     * @return PurchaseInterface
     */
    function setAffiliation($affiliation);

    /**
     * @param $coupon
     * @return PurchaseInterface
     */
    function setCoupon($coupon);

    /**
     * @param $shipping
     * @return PurchaseInterface
     */
    function setShipping($shipping);

    /**
     * @param $tax
     * @return PurchaseInterface
     */
    function setTax($tax);

    /**
     * @param PurchaseItemInterface $purchaseItem
     * @return PurchaseInterface
     */
    function addItem($purchaseItem);

    /**
     * @return array
     */
    function getParams();
}
