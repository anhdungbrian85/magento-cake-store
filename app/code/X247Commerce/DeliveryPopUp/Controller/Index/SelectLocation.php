<?php

namespace X247Commerce\DeliveryPopUp\Controller\Index;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Customer\Model\Session as CustomerSession;

class SelectLocation extends \Amasty\Storelocator\Controller\Index\Ajax
{
	protected CustomerSession $customerSession;
	protected JsonFactory $resultJsonFactory;

	public function __construct(
        \Magento\Framework\App\Action\Context $context,
        CustomerSession $customerSession,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->resultJsonFactory = $resultJsonFactory;
    }
	public function execute()
    {
    	$data = $this->getRequest()->getPostValue();
        if (!empty($data["location_id"])) {
            $this->customerSession->setStoreLocationId($data["location_id"]);
        }
    	
    }
}
