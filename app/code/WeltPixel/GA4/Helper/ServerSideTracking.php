<?php

namespace WeltPixel\GA4\Helper;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ServerSideTracking extends Data
{
    /**
     * @return boolean
     */
    public function isServerSideTrakingEnabled() {
        return (boolean) ($this->_gtmOptions['serverside_measurement']['enable'] ?? false);
    }

    /**
     * @return string
     */
    public function getMeasurementId() {
        return trim($this->_gtmOptions['serverside_measurement']['measurement_id'] ?? '');
    }

    /**
     * @return string
     */
    public function getApiSecret() {
        return trim($this->_gtmOptions['serverside_measurement']['api_secret'] ?? '');
    }

    /**
     * @return bool
     */
    public function getDebugFileEnabled() {
        return (boolean) ($this->_gtmOptions['serverside_measurement']['enable_file_log'] ?? false);
    }

    /**
     * @return bool
     */
    public function getDebugCollectEnabled() {
        return (boolean) ($this->_gtmOptions['serverside_measurement']['enable_debug_collect'] ?? false);
    }

    /**
     * @return bool
     */
    public function isDataLayerEventDisabled() {
        return (boolean)($this->_gtmOptions['serverside_measurement']['disable_datalayer_events'] ?? false);
    }

    /**
     * @return bool
     */
    public function sendUserIdInEvents() {
        return (boolean)($this->_gtmOptions['serverside_measurement']['send_user_id'] ?? false);
    }

    /**
     * @return array
     */
    public function getTrackedEvents() {
        $trackedEvents = $this->_gtmOptions['serverside_measurement']['events'] ?? '';
        return explode(',', $trackedEvents);
    }

    /**
     * @param $eventName
     * @return bool
     */
    public function shouldEventBeTracked($eventName) {
        $availableEvents = $this->getTrackedEvents();
        return in_array($eventName, $availableEvents);
    }

    /**
     * @deplacated
     * @return string
     */
    public function getCustomerSessionId() {
        $customerId = 'GUEST';
        $visitorData = $this->session->getVisitorData();
        if (isset($visitorData['visitor_id'])) {
            $customerId .= '_' . $visitorData['visitor_id'];
        }
        return $customerId;
    }

    /**
     * @return string
     */
    public function getClientId() {
        $clientId = 'client_id';
        $gaCookie = $this->cookieManager->getCookie('_ga');
        if (isset($gaCookie) && strlen($gaCookie)) {
            $cookieExploded = explode('.', $gaCookie);
            if (isset($cookieExploded[2])) {
                $clientId = $cookieExploded[2];
            }
            if (isset($cookieExploded[3])) {
                $clientId .= '.' . $cookieExploded[3];
            }
        }

        return $clientId;
    }

    /**
     * @return array
     */
    public function getSessionIdAndTimeStamp() {
        $sessionId = false;
        $timestamp = false;

        $measurementId = $this->getMeasurementId();
        $gaMeasurementCookie = $this->cookieManager->getCookie('_ga_' . str_replace('G-' ,'', $measurementId));
        if (isset($gaMeasurementCookie) && strlen($gaMeasurementCookie)) {
            $cookieExploded = explode('.', $gaMeasurementCookie);
            if (isset($cookieExploded[2])) {
                $sessionId = $cookieExploded[2];
            }
            if (isset($cookieExploded[5])) {
                $timestamp = $cookieExploded[5] * 1000000;
            }
        }

        return [
            'session_id' => $sessionId,
            'timestamp' => $timestamp
        ];
    }
}
