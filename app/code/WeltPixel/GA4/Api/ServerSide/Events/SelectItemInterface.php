<?php

namespace WeltPixel\GA4\Api\ServerSide\Events;

use WeltPixel\GA4\Model\ServerSide\Events\SelectItem;

interface SelectItemInterface
{
    /**
     * @param $clientId
     * @return SelectItemInterface
     */
    function setClientId($clientId);

    /**
     * @param $sessionId
     * @return SelectItemInterface
     */
    function setSessionId($sessionId);

    /**
     * @param $timestamp
     * @return SelectItemInterface
     */
    function setTimestamp($timestamp);

    /**
     * @param $userId
     * @return SelectItemInterface
     */
    function setUserId($userId);

    /**
     * @param $listId
     * @return SelectItemInterface
     */
    function setItemListId($listId);

    /**
     * @param $listName
     * @return SelectItemInterface
     */
    function setItemListName($listName);

    /**
     * @param SelectItemItemInterface $selectItemItem
     * @return SelectItemInterface
     */
    function addItem($selectItemItem);

    /**
     * @return array
     */
    function getParams();
}
