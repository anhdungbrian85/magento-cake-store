<?php

namespace X247Commerce\Checkout\Plugin;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Http\Context;
use X247Commerce\Checkout\Api\StoreLocationContextInterface;
use Magento\Framework\App\Action\AbstractAction;
use Magento\Framework\App\RequestInterface;

class HttpContext
{
    protected CheckoutSession $checkoutSession;
    protected $context;

    public function __construct(
        CheckoutSession $checkoutSession,
        Context $context
    )
    {
        $this->checkoutSession = $checkoutSession;
        $this->context = $context;
    }

    public function beforeDispatch(AbstractAction $subject, RequestInterface $request)
    {
        $storeLocationId = $this->checkoutSession->getStoreLocationId();
        $deliveryType = $this->checkoutSession->getDeliveryType();
        $defaultStoreLocationIdContext = 0;
        $defaultDeliveryTypeContext = 0;
        // $subject->setValue('store_location_id', $storeLocationId, $defaultStoreLocationIdContext);
        $this->context->setValue(
                'store_location_id',
                $storeLocationId,
                $defaultStoreLocationIdContext
            );
        $this->context->setValue(
            'delivery_type',
            $deliveryType,
            $defaultDeliveryTypeContext
        );
    }
}
