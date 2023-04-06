<?php

namespace WeltPixel\GA4\Api\ServerSide\Events;

interface ViewItemInterface
{
    /**
     * @param $clientId
     * @return ViewItemInterface
     */
    function setClientId($clientId);

    /**
     * @param $sessionId
     * @return ViewItemInterface
     */
    function setSessionId($sessionId);

    /**
     * @param $timestamp
     * @return ViewItemInterface
     */
    function setTimestamp($timestamp);

    /**
     * @param $userId
     * @return ViewItemInterface
     */
    function setUserId($userId);

    /**
     * @param $currency
     * @return ViewItemInterface
     */
    function setCurrency($currency);

    /**
     * @param $value
     * @return ViewItemInterface
     */
    function setValue($value);

    /**
     * @param ViewItemItemInterface $viewItemItem
     * @return ViewItemInterface
     */
    function addItem($viewItemItem);

    /**
     * @return array
     */
    function getParams();
}
