<?php

namespace WeltPixel\GA4\Model\ServerSide\Events;

use WeltPixel\GA4\Api\ServerSide\Events\AddPaymentInfoInterface;
use WeltPixel\GA4\Api\ServerSide\Events\AddPaymentInfoItemInterface;

class AddPaymentInfo implements AddPaymentInfoInterface
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
    protected $addPaymentInfoItems;

    /**
     * @var array
     */
    protected $addPaymentInfoEvent;

    public function __construct()
    {
        $this->addPaymentInfoEvent = [];
        $this->payloadData = [];
        $this->payloadData['events'] = [];
        $this->addPaymentInfoEvent['name'] = 'add_payment_info';
        $this->eventParams = [];
        $this->addPaymentInfoItems = [];
    }

    /**
     * @return array
     */
    public function getParams()
    {
        $this->eventParams['items'] = $this->addPaymentInfoItems;
        $this->addPaymentInfoEvent['params'] = $this->eventParams;

        array_push($this->payloadData['events'], $this->addPaymentInfoEvent);
        return $this->payloadData;
    }

    /**
     * @param $clientId
     * @return AddPaymentInfoInterface
     */
    public function setClientId($clientId)
    {
        $this->payloadData['client_id'] = (string)$clientId;
        return $this;
    }

    /**
     * @param $sessionId
     * @return AddPaymentInfoInterface
     */
    public function setSessionId($sessionId)
    {
        $this->eventParams['session_id'] =(string)$sessionId;
        return $this;
    }

    /**
     * @param $timestamp
     * @return AddPaymentInfoInterface
     */
    public function setTimestamp($timestamp)
    {
        $this->payloadData['timestamp_micros'] = (string)$timestamp;
        return $this;
    }

    /**
     * @param $userId
     * @return AddPaymentInfoInterface
     */
    public function setUserId($userId)
    {
        $this->payloadData['user_id'] = (string)$userId;
        return $this;
    }

    /**
     * @param $currency
     * @return AddPaymentInfoInterface
     */
    public function setCurrency($currency)
    {
        $this->eventParams['currency'] = $currency;
        return $this;
    }

    /**
     * @param $value
     * @return AddPaymentInfoInterface
     */
    public function setValue($value)
    {
        $this->eventParams['value'] = $value;
        return $this;
    }

    /**
     * @param $coupon
     * @return AddPaymentInfoInterface
     */
    public function setCoupon($coupon)
    {
        $this->eventParams['coupon'] = $coupon;
        return $this;
    }

    /**
     * @param $paymentType
     * @return AddPaymentInfoInterface
     */
    public function setPaymentType($paymentType)
    {
        $this->eventParams['payment_type'] = $paymentType;
        return $this;
    }

    /**
     * @param  AddPaymentInfoItemInterface $addPaymentInfoItem
     * @return AddPaymentInfoInterface
     */
    function addItem($addPaymentInfoItem)
    {
        $this->addPaymentInfoItems[] = $addPaymentInfoItem->getParams();
        return $this;
    }
}
