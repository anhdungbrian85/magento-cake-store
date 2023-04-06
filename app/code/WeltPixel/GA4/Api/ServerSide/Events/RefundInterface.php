<?php

namespace WeltPixel\GA4\Api\ServerSide\Events;

interface RefundInterface
{
    /**
     * @param $clientId
     * @return RefundInterface
     */
    function setClientId($clientId);

    /**
     * @param $sessionId
     * @return RefundInterface
     */
    function setSessionId($sessionId);

    /**
     * @param $timestamp
     * @return RefundInterface
     */
    function setTimestamp($timestamp);


    /**
     * @param $userId
     * @return RefundInterface
     */
    function setUserId($userId);

    /**
     * @param $currency
     * @return RefundInterface
     */
    function setCurrency($currency);

    /**
     * @param $transactionId
     * @return RefundInterface
     */
    function setTransactionId($transactionId);

    /**
     * @param $value
     * @return RefundInterface
     */
    function setValue($value);

    /**
     * @param $affiliation
     * @return RefundInterface
     */
    function setAffiliation($affiliation);

    /**
     * @param $coupon
     * @return RefundInterface
     */
    function setCoupon($coupon);

    /**
     * @param $shipping
     * @return RefundInterface
     */
    function setShipping($shipping);

    /**
     * @param $tax
     * @return RefundInterface
     */
    function setTax($tax);

    /**
     * @param RefundItemInterface $refundItem
     * @return RefundInterface
     */
    function addItem($refundItem);

    /**
     * @return array
     */
    function getParams();
}
