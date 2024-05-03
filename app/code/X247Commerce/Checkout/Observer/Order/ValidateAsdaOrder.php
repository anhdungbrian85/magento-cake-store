<?php

namespace X247Commerce\Checkout\Observer\Order;

use Amasty\StorePickupWithLocator\Api\Data\OrderInterface;
use Amasty\StorePickupWithLocator\Api\Data\OrderInterfaceFactory;
use Amasty\StorePickupWithLocator\Api\Data\QuoteInterface;
use Amasty\StorePickupWithLocator\Model\Carrier\Shipping;
use Amasty\StorePickupWithLocator\Model\OrderRepository;
use Amasty\StorePickupWithLocator\Model\Quote;
use Amasty\StorePickupWithLocator\Model\QuoteRepository;
use Amasty\StorePickupWithLocator\Model\Sales\AddressResolver;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use X247Commerce\Checkout\Model\CheckoutLocationParams;

class ValidateAsdaOrder implements ObserverInterface
{
    private $quoteRepository;
    private $orderRepository;
    private $orderFactory;
    private $searchCriteriaBuilder;
    private $orderAddressResolver;
    private $checkoutLocationParams;

    public function __construct(
        QuoteRepository $quoteRepository,
        OrderRepository $orderRepository,
        OrderInterfaceFactory $orderFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AddressResolver $orderAddressResolver,
        CheckoutLocationParams $checkoutLocationParams
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->orderRepository = $orderRepository;
        $this->orderFactory = $orderFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderAddressResolver = $orderAddressResolver;
        $this->checkoutLocationParams = $checkoutLocationParams;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(EventObserver $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        if (!$order = $observer->getEvent()->getOrder()) {
            return $this;
        }

        if ($order->getShippingMethod() !== Shipping::SHIPPING_NAME) {
            return $this;
        }

        $this->searchCriteriaBuilder->addFilter(QuoteInterface::QUOTE_ID, $order->getQuoteId());
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $quoteList = $this->quoteRepository->getList($searchCriteria);

        $leadDelivery = 0;
        foreach ($order->getAllItems() as $item) {
            if ($item->getProduct()->getLeadDelivery() > $leadDelivery) {
                $leadDelivery = $item->getProduct()->getLeadDelivery();
            }
        }

        /** @var Quote $quote */
        foreach ($quoteList->getItems() as $quote) {
            $locationId = $quote->getStoreId();
            $pickDate = $quote->getDate();
            $pickTime = $quote->getTimeFrom();
        }
        if (in_array($locationId, $this->checkoutLocationParams->getAsdaLocationId())) {
            if(strtotime("now") + $leadDelivery*3600 > strtotime(date("Y-m-d 16:00:00"))) {
                if (strtotime($pickDate) < strtotime(date("Y-m-d"). " + 2 days")) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __("Please choose other Pickup Date")
                      );
                }
            }
        }

        return $this;
    }
}