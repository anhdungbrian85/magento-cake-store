<?php

namespace X247Commerce\StoreLocator\Controller\Cart;

use Magento\Framework\App\Action\Context;
use X247Commerce\StoreLocatorSource\Model\ResourceModel\LocatorSourceResolver;

class Validate extends \Magento\Framework\App\Action\Action
{

    protected $quoteRepository;

    protected $checkoutSession;

    protected $locatorSourceResolver;

    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Checkout\Model\Session $checkoutSession,
        LocatorSourceResolver $locatorSourceResolver,
        Context $context
    ) {
        parent::__construct($context);
        $this->locatorSourceResolver = $locatorSourceResolver;
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
    }

    public function execute()
    {
        $data = $this->getRequest()->getParams();
        $locationId = $data['location_id'];
        $quoteId = (int)$this->checkoutSession->getQuote()->getId();
        if ($quoteId && $locationId) {
            $quote = $this->quoteRepository->get($quoteId);
            foreach ($quote->getAllVisibleItems() as $item) {
                $available = $this->locatorSourceResolver->checkProductAvailableInStore($locationId, $item);
                if (!$available) {
                    $result = [
                        'status' => 500,
                        'abc' => __('Some of the products are out stock!')
                    ];
                    return $this->getResponse()->setBody(json_encode($result));
                }
            }
        } else {
            $result = [
                'status' => 500,
                'abc' => __('Invalid params!')
            ];
            return $this->getResponse()->setBody(json_encode($result));
        }
        $result = [
            'status' => 200,
            'abc' => __('Okay!')
        ];
        return $this->getResponse()->setBody(json_encode($result));
    }
}
