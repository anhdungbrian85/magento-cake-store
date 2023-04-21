<?php

namespace WeltPixel\GA4\Api\ServerSide\Events;

interface LoginInterface
{
    /**
     * @param $clientId
     * @return LoginInterface
     */
    function setClientId($clientId);

    /**
     * @param $sessionId
     * @return LoginInterface
     */
    function setSessionId($sessionId);

    /**
     * @param $timestamp
     * @return LoginInterface
     */
    function setTimestamp($timestamp);

    /**
     * @param $userId
     * @return LoginInterface
     */
    function setUserId($userId);

    /**
     * @param $method
     * @return LoginInterface
     */
    function setMethod($method);

    /**
     * @return array
     */
    function getParams();
}
