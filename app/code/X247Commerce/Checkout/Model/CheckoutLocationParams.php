<?php

namespace X247Commerce\Checkout\Model;

use Magento\Checkout\Model\Session as CheckoutSession;
use X247Commerce\Checkout\Api\StoreLocationContextInterface;
use Amasty\Storelocator\Model\LocationFactory;
use Amasty\StorePickupWithLocator\Model\QuoteFactory as PickupQuoteFactory;
use Magento\Quote\Model\Quote\AddressFactory;
use Amasty\StorePickupWithLocator\Api\QuoteRepositoryInterface as PickupQuoteRepositoryInterface;
use Amasty\StorePickupWithLocator\Model\Location\LocationsAvailability;
use Amasty\StorePickupWithLocator\Model\LocationProvider;
use Amasty\StorePickupWithLocator\Model\ConfigProvider;
use Amasty\StorePickupWithLocator\Model\ScheduleProvider;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Config\ScopeConfigInterface;

class CheckoutLocationParams
{
    const XML_PATH_DELIVERY_WEEKDAY_TIMESLOT = 'amasty_checkout/delivery_date/available_hours';
    const XML_PATH_DELIVERY_WEEKEND_TIMESLOT = 'amasty_checkout/delivery_date/weekend_available_hours';

    protected $checkoutSession;
    protected $storeLocationContext;
    protected $logger;
    protected $locationFactory;
    protected $addressFactory;
    protected $pickupQuoteFactory;
    protected $pickupQuoteRepository;
    protected $locationData;
    protected $urlBuilder;
    protected $configProvider;
    protected $locationProvider;
    protected $scheduleProvider;
    protected $locationsAvailability;
    protected $_resource;
    protected ScopeConfigInterface $scopeConfig;

    public function __construct(
        CheckoutSession $checkoutSession,
        StoreLocationContextInterface $storeLocationContext,
        LocationFactory $locationFactory,
        AddressFactory $addressFactory,
        PickupQuoteFactory $pickupQuoteFactory,
        PickupQuoteRepositoryInterface $pickupQuoteRepository,
        UrlInterface $urlBuilder,
        ConfigProvider $configProvider,
        LocationProvider $locationProvider,
        ScheduleProvider $scheduleProvider,
        LocationsAvailability $locationsAvailability,
        \Psr\Log\LoggerInterface $logger,
        ResourceConnection $resource,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->storeLocationContext = $storeLocationContext;
        $this->locationFactory = $locationFactory;
        $this->addressFactory = $addressFactory;
        $this->pickupQuoteFactory = $pickupQuoteFactory;
        $this->pickupQuoteRepository = $pickupQuoteRepository;
        $this->urlBuilder = $urlBuilder;
        $this->configProvider = $configProvider;
        $this->locationProvider = $locationProvider;
        $this->scheduleProvider = $scheduleProvider;
        $this->locationsAvailability = $locationsAvailability;
        $this->logger = $logger;
        $this->_resource = $resource;
        $this->scopeConfig = $scopeConfig;

    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getConfig()
    {
        $quote = $this->checkoutSession->getQuote();

        $locationId =  $this->storeLocationContext->getStoreLocationId() ?? $quote->getStoreLocationId();
        $deliveryType = $this->storeLocationContext->getDeliveryType() ??  $quote->getDeliveryType();

        return [
            'storeLocationId' => $locationId,
            'deliveryType' => $deliveryType,
            'initLeadDeliveryValue' =>  $this->getInitLeadDeliveryValue(),
            'amastySelectedPickup' => [
                'am_pickup_curbside' => [],
                'am_pickup_date' => '',
                'am_pickup_store' => $locationId,
                'am_pickup_time' => ''
            ],
            'amastyLocations' => $this->getLocationData(),
            'asdaLocationIds' => $this->getAsdaLocationId(),
            'deliveryPostcode' => $deliveryType == 1 ? $this->checkoutSession->getCustomerPostcode() : '',
            'deliveryDateTimeSlots' => $this->getDeliveryDateTimeSlots(),
            'store_location_holiday' => $this->getStoreHoliday($locationId)
        ];
    }

    /**
     * @return array
     */
    private function getLocationData() {
        $locationItems = $this->locationProvider->getLocationCollection();
        $scheduleToLocationsMap = [];
        foreach ($locationItems as $locationKey => $location) {
            $scheduleId = $location['schedule_id'];
            if ($scheduleId) {
                $scheduleToLocationsMap[$scheduleId][] = $locationKey;
            }
        }

        $scheduleData = $this->scheduleProvider->getScheduleDataArray(array_keys($scheduleToLocationsMap));

        foreach ($scheduleData['emptySchedules'] as $scheduleId) {
            foreach ($scheduleToLocationsMap[$scheduleId] as $locationKey) {
                unset($locationItems[$locationKey]);
            }
        }

        $locationItems = array_values($locationItems);

        if (empty($locationItems)) {
            $this->locationsAvailability->setIsAvailable(false);
        }

        return [
            'stores' => $locationItems,
            'schedule_data' => $scheduleData,
            'website_id' => $this->locationProvider->getQuote()->getStore()->getWebsiteId(),
            'store_id' => $this->locationProvider->getQuote()->getStore()->getId(),
            'multiple_addresses_url' => '',
            'contact_us_url' => $this->getContactUsUrl()
        ];
    }

    public function getAsdaLocationId() {
        // $locationItems = $this->locationProvider->getPreparedCollection();
        // foreach ($locationItems as $location) {
        // }
        $connection = $this->_resource->getConnection();
        $tableName = $connection->getTableName('store_location_asda_link');

        $query = $connection->select()->from($tableName, ['asda_location_id']);

        $fetchData = $connection->fetchCol($query);
        return $fetchData;
    }

    public function getStoreHoliday() {
        
        $connection = $this->_resource->getConnection();
        $tableName = $connection->getTableName('store_location_holiday');

        $query = $connection->select()->from($tableName);

        $fetchData = $connection->fetchAll($query);
        return $fetchData;
    }

    /**
     * @return string
     */
    private function getContactUsUrl()
    {
        return $this->urlBuilder->getUrl('contact');
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getInitLeadDeliveryValue()
    {
        $quote = $this->checkoutSession->getQuote();
        $leadDelivery = 0;
        foreach ($quote->getAllItems() as $item) {
            if ($leadDelivery < $item->getProduct()->getData('lead_delivery')) {
                $leadDelivery = $item->getProduct()->getData('lead_delivery');
            }
        }
        return $leadDelivery;
    }


    private function getDeliveryDateTimeSlots()
    {
        return [
            'weekday' => $this->scopeConfig->getValue(self::XML_PATH_DELIVERY_WEEKDAY_TIMESLOT, ScopeConfigInterface::SCOPE_TYPE_DEFAULT),
            'weekend' => $this->scopeConfig->getValue(self::XML_PATH_DELIVERY_WEEKEND_TIMESLOT, ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
        ];
    }


}
