<?php

namespace WeltPixel\GA4\Api\ServerSide\Events;

interface BeginCheckoutInterface
{
    /**
     * @param $clientId
     * @return BeginCheckoutInterface
     */
    function setClientId($clientId);

    /**
     * @param $sessionId
     * @return BeginCheckoutInterface
     */
    function setSessionId($sessionId);

    /**
     * @param $timestamp
     * @return BeginCheckoutInterface
     */
    function setTimestamp($timestamp);

    /**
     * @param $userId
     * @return BeginCheckoutInterface
     */
    function setUserId($userId);

    /**
     * @param $currency
     * @return BeginCheckoutInterface
     */
    function setCurrency($currency);

    /**
     * @param $value
     * @return BeginCheckoutInterface
     */
    function setValue($value);

    /**
     * @param $coupon
     * @return BeginCheckoutInterface
     */
    function setCoupon($coupon);

    /**
     * @param BeginCheckoutItemInterface $beginCheckoutItem
     * @return BeginCheckoutInterface
     */
    function addItem($beginCheckoutItem);

    /**
     * @return array
     */
    function getParams();
}
