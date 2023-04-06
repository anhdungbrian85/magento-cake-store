<?php

namespace WeltPixel\GA4\Model\ServerSide\Events;

use WeltPixel\GA4\Api\ServerSide\Events\LoginInterface;

class Login implements LoginInterface
{
    /**
     * @var array
     */
    protected $payloadData;

    /**
     * @var array
     */
    protected $eventParams;

    /**
     * @var array
     */
    protected $loginEvent;

    public function __construct()
    {
        $this->loginEvent = [];
        $this->payloadData = [];
        $this->payloadData['events'] = [];
        $this->loginEvent['name'] = 'login';
        $this->eventParams = [];
        $this->eventParams['method'] = 'Magento';
    }

    /**
     * @return array
     */
    public function getParams()
    {
        $this->loginEvent['params'] = $this->eventParams;

        array_push($this->payloadData['events'], $this->loginEvent);
        return $this->payloadData;
    }

    /**
     * @param $clientId
     * @return LoginInterface
     */
    public function setClientId($clientId)
    {
        $this->payloadData['client_id'] = (string)$clientId;
        return $this;
    }

    /**
     * @param $sessionId
     * @return LoginInterface
     */
    public function setSessionId($sessionId)
    {
        $this->eventParams['session_id'] =(string)$sessionId;
        return $this;
    }

    /**
     * @param $timestamp
     * @return LoginInterface
     */
    public function setTimestamp($timestamp)
    {
        $this->payloadData['timestamp_micros'] = (string)$timestamp;
        return $this;
    }

    /**
     * @param $userId
     * @return LoginInterface
     */
    public function setUserId($userId)
    {
        $this->payloadData['user_id'] = (string)$userId;
        return $this;
    }

    /**
     * @param $method
     * @return LoginInterface
     */
    public function setMethod($method)
    {
        $this->eventParams['method'] = $method;
        return $this;
    }
}
