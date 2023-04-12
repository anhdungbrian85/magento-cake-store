<?php

namespace WeltPixel\GA4\Model\ServerSide\Events;

use WeltPixel\GA4\Api\ServerSide\Events\RefundInterface;
use WeltPixel\GA4\Api\ServerSide\Events\RefundItemInterface;

class Refund implements RefundInterface
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
    protected $refundItems;

    /**
     * @var array
     */
    protected $refundEvent;

    public function __construct()
    {
        $this->refundEvent = [];
        $this->payloadData = [];
        $this->payloadData['events'] = [];
        $this->refundEvent['name'] = 'refund';
        $this->eventParams = [];
        $this->refundItems = [];
    }

    /**
     * @return array
     */
    public function getParams()
    {
        $this->eventParams['items'] = $this->refundItems;
        $this->refundEvent['params'] = $this->eventParams;

        array_push($this->payloadData['events'], $this->refundEvent);
        return $this->payloadData;
    }

    /**
     * @param $clientId
     * @return RefundInterface
     */
    public function setClientId($clientId)
    {
        $this->payloadData['client_id'] = (string)$clientId;
        return $this;
    }

    /**
     * @param $sessionId
     * @return RefundInterface
     */
    public function setSessionId($sessionId)
    {
        $this->eventParams['session_id'] =(string)$sessionId;
        return $this;
    }

    /**
     * @param $timestamp
     * @return RefundInterface
     */
    public function setTimestamp($timestamp)
    {
        $this->payloadData['timestamp_micros'] = (string)$timestamp;
        return $this;
    }

    /**
     * @param $userId
     * @return RefundInterface
     */
    public function setUserId($userId)
    {
        $this->payloadData['user_id'] = (string)$userId;
        return $this;
    }

    /**
     * @param $currency
     * @return RefundInterface
     */
    public function setCurrency($currency)
    {
        $this->eventParams['currency'] = $currency;
        return $this;
    }

    /**
     * @param $transactionId
     * @return RefundInterface
     */
    public function setTransactionId($transactionId)
    {
        $this->eventParams['transaction_id'] = $transactionId;
        return $this;
    }

    /**
     * @param $value
     * @return RefundInterface
     */
    public function setValue($value)
    {
        $this->eventParams['value'] = $value;
        return $this;
    }

    /**
     * @param $affiliation
     * @return RefundInterface
     */
    public function setAffiliation($affiliation)
    {
        $this->eventParams['affiliation'] = $affiliation;
        return $this;
    }

    /**
     * @param $coupon
     * @return RefundInterface
     */
    public function setCoupon($coupon)
    {
        $this->eventParams['coupon'] = $coupon;
        return $this;
    }

    /**
     * @param $shipping
     * @return RefundInterface
     */
    public function setShipping($shipping)
    {
        $this->eventParams['shipping'] = $shipping;
        return $this;
    }

    /**
     * @param $tax
     * @return RefundInterface
     */
    public function setTax($tax)
    {
        $this->eventParams['tax'] = $tax;
        return $this;
    }

    /**
     * @param RefundItemInterface $refundItem
     * @return RefundInterface
     */
    function addItem($refundItem)
    {
        $this->refundItems[] = $refundItem->getParams();
        return $this;
    }
}
