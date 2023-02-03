<?php

namespace X247Commerce\ChangeOrderStatus\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{

	const XML_CHANGE_STATUS_ORDER_DAY_NUMBER = 'changestatusorder/general/day_number';

	public function getNumberDayChangeStatus() 
	{
		return $this->scopeConfig->getValue(
			self::XML_CHANGE_STATUS_ORDER_DAY_NUMBER , ScopeInterface::SCOPE_STORE
		);
	}
}

