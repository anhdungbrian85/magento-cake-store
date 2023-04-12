<?php

namespace WeltPixel\GA4\Api\ServerSide\Events;

interface SignupInterface
{
    /**
     * @param $clientId
     * @return SignupInterface
     */
    function setClientId($clientId);

    /**
     * @param $sessionId
     * @return SignupInterface
     */
    function setSessionId($sessionId);

    /**
     * @param $timestamp
     * @return SignupInterface
     */
    function setTimestamp($timestamp);

    /**
     * @param $userId
     * @return SignupInterface
     */
    function setUserId($userId);

    /**
     * @param $method
     * @return SignupInterface
     */
    function setMethod($method);

    /**
     * @return array
     */
    function getParams();
}
