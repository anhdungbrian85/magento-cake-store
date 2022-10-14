<?php

namespace X247Commerce\DeliveryPopUp\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{

	const XML_PATH_DELIVERY_POPUP_ENABLE = 'deliverypopup/general/enable';
	const XML_PATH_DELIVERY_POPUP_SEARCH_RADIUS = 'deliverypopup/general/search_radius';
	const XML_PATH_DELIVERY_POPUP_RESULT_RECORDS = 'deliverypopup/general/total_records';


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

}	