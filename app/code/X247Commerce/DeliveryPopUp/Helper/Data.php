<?php

namespace X247Commerce\DeliveryPopUp\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{

	const XML_PATH_DELIVERY_POPUP_ENABLE = 'deliverypopup/general/enable';
	const XML_PATH_DELIVERY_POPUP_SEARCH_RADIUS = 'deliverypopup/general/search_radius';
	const XML_PATH_DELIVERY_POPUP_RESULT_RECORDS = 'deliverypopup/general/total_records';
	const XML_PATH_DISTANCE_UNIT_CONFIG = 'amlocator/locator/visual_settings/distance';


	public function isEnabledPopup()
	{
		return $this->scopeConfig->getValue(
			self::XML_PATH_DELIVERY_POPUP_ENABLE , ScopeInterface::SCOPE_STORE
		);
	}

	public function getPopupSearchRadius()
	{
		return $this->scopeConfig->getValue(
			self::XML_PATH_DELIVERY_POPUP_SEARCH_RADIUS , ScopeInterface::SCOPE_STORE
		);
	}

	public function getPopupTotalStoresResult()
	{
		return $this->scopeConfig->getValue(
			self::XML_PATH_DELIVERY_POPUP_RESULT_RECORDS , ScopeInterface::SCOPE_STORE
		);
	}

	public function getDistanceUnitSetting()
	{
		return $this->scopeConfig->getValue(
			self::XML_PATH_DISTANCE_UNIT_CONFIG , ScopeInterface::SCOPE_STORE
		);
	}

	/**
	 * calculate distancse from 2 place by latitude and longtitude
	 * @var float $lat1
	 * @var float $lon1
	 * @var float $lat2
	 * @var float $lon2
	 * @var string $unit
	 * @return float
	 **/

	public function calculateDistance($lat1, $lon1, $lat2, $lon2, $unit)
	{

		if (($lat1 == $lat2) && ($lon1 == $lon2)) {
			return 0;
		} else {
			$theta = $lon1 - $lon2;
			$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
			$dist = acos($dist);
			$dist = rad2deg($dist);
			$miles = $dist * 60 * 1.1515;
			$unit = strtoupper($unit);

			if ($unit == "km") {
			  return round(($miles * 1.609344), 1).'km';
			}  else {
			  return round($miles, 1).'miles';
			}
		}
	}

    public function calculateDistanceWithOutUnit($lat1, $lon1, $lat2, $lon2, $unit)
    {

        if (($lat1 == $lat2) && ($lon1 == $lon2)) {
            return 0;
        } else {
            $theta = $lon1 - $lon2;
            $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
            $dist = acos($dist);
            $dist = rad2deg($dist);
            $miles = $dist * 60 * 1.1515;
            $unit = strtoupper($unit);

            if ($unit == "km") {
                return round(($miles * 1.609344), 1);
            }  else {
                return round($miles, 1);
            }
        }
    }
}
