<?php

namespace X247Commerce\Checkout\Plugin;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Http\Context;
use X247Commerce\Checkout\Api\StoreLocationContextInterface;
use Magento\Framework\App\Action\AbstractAction;
use Magento\Framework\App\RequestInterface;

class HttpContext 
{
    protected CustomerSession $customerSession;
    protected $context;

    public function __construct(
        CustomerSession $customerSession,
        Context $context
    )
    {
        $this->customerSession = $customerSession;
        $this->context = $context;
    }

    public function beforeDispatch(AbstractAction $subject, RequestInterface $request)
    {
        $storeLocationId = $this->customerSession->getStoreLocationId();
        $defaultStoreLocationIdContext = 0;
        // $subject->setValue('store_location_id', $storeLocationId, $defaultStoreLocationIdContext);
        $this->context->setValue(
                'store_location_id',
                $storeLocationId,
                $defaultStoreLocationIdContext
            );
    }
}
