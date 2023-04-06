<?php

namespace WeltPixel\GA4\Api\ServerSide\Events;

interface SearchInterface
{
    /**
     * @param $clientId
     * @return SearchInterface
     */
    function setClientId($clientId);

    /**
     * @param $sessionId
     * @return SearchInterface
     */
    function setSessionId($sessionId);

    /**
     * @param $timestamp
     * @return SearchInterface
     */
    function setTimestamp($timestamp);


    /**
     * @param $userId
     * @return SearchInterface
     */
    function setUserId($userId);

    /**
     * @param $searchTerm
     * @return SearchInterface
     */
    function setSearchTerm($searchTerm);

    /**
     * @return array
     */
    function getParams();
}
