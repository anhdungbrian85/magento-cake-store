<?php

namespace X247Commerce\Checkout\Observer\Order;

use Amasty\StorePickupWithLocator\Api\Data\OrderInterfaceFactory;
use Amasty\StorePickupWithLocator\Api\Data\QuoteInterface;
use Amasty\StorePickupWithLocator\Model\Carrier\Shipping;
use Amasty\StorePickupWithLocator\Model\Quote;
use Amasty\StorePickupWithLocator\Model\QuoteRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use X247Commerce\Checkout\Model\CheckoutLocationParams;
use Amasty\Storelocator\Model\LocationFactory;
use X247Commerce\HolidayOpeningTime\Model\ResourceModel\StoreLocationHoliday\CollectionFactory as HolidayCollectionFactory;

class ValidateOrderDateTime implements ObserverInterface
{
    protected $quoteRepository;
    protected $searchCriteriaBuilder;
    protected $checkoutLocationParams;
    protected LocationFactory $locationFactory;
    protected HolidayCollectionFactory $holidayCollectionFactory;

    public function __construct(
        QuoteRepository $quoteRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CheckoutLocationParams $checkoutLocationParams,
        LocationFactory $locationFactory,
        HolidayCollectionFactory $holidayCollectionFactory
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->checkoutLocationParams = $checkoutLocationParams;
        $this->locationFactory = $locationFactory;
        $this->holidayCollectionFactory = $holidayCollectionFactory;
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

        $location = $this->locationFactory->create()->load($locationId);
        $asdaLeadDeliveryDay = $location->getData('asda_lead_delivery');
        $holidays = $this->holidayCollectionFactory->create();
        $holidays->addFieldToFilter('store_location_id', $locationId);
        $holidaysArr = [];
        foreach ($holidays as $holiday) {
            if ($holiday->getDisablePickup()) {
                $holidaysArr[] = strtotime($holiday->getDate());
            }
        }

        if (strtotime(date("Y-m-d")) > strtotime($pickDate)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __("Your pickup date is invalid. Please choose another pickup Date!")
            );
        }

        $dayIncrease = $asdaLeadDeliveryDay;
        if (in_array($locationId, $this->checkoutLocationParams->getAsdaLocationId())) {
            // ASDA store
            $beforeCutOfTime = strtotime("now") + $leadDelivery*3600 < strtotime(date("Y-m-d 16:00:00"));

            if (!$beforeCutOfTime) {
                // Should be next day
                $dayIncrease++;
            }

            if (count($holidaysArr)) {
                $minAvaiIncrDay = $beforeCutOfTime ? 0 : 1;
                $maxAvaiIncrDay = $beforeCutOfTime ? $asdaLeadDeliveryDay : ($asdaLeadDeliveryDay + 1);
                // If there are holidays during lead delivery, then increase more delay days
                for ($i = $minAvaiIncrDay; $i < $maxAvaiIncrDay; $i++) {
                    $strToTimeDay = strtotime(date("Y-m-d"));
                    $strToTimeDay += $i*86400;
                    if (in_array($strToTimeDay, $holidaysArr)) {
                        $dayIncrease++;
                    }
                }
            }

            if (strtotime($pickDate) < strtotime(date("Y-m-d"). " + $dayIncrease days")) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("We cannot offer your cake at that time. Please choose another pickup date!")
                );
            }
        }   else {
            // normal store
            $hsInPickupTime = date("h:i", $pickTime);
            $dateTimePickupTime = trim(str_replace('00:00:00', $hsInPickupTime, $pickDate));

            if ((time() + $leadDelivery*3600) > $dateTimePickupTime) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("We cannot offer your cake at that time. Please choose another date!")
                );
            }
        }

        return $this;
    }

}
