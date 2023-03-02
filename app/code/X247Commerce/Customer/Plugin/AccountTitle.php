<?php

namespace X247Commerce\Customer\Plugin;

class AccountTitle
{

	public function afterExecute( \Magento\Customer\Controller\Account\Index $subject, $resultPage)
	{	
		$resultPage->getConfig()->getTitle()->set((__('My Account')));
		return $resultPage;
	}

}