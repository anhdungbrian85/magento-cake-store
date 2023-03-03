<?php

namespace X247Commerce\HolidayOpeningTime\Observer;

use Amasty\StorePickupWithLocatorMSI\Api\LocationSourceRepositoryInterface;
use Amasty\StorePickupWithLocatorMSI\Model\LocationSourceFactory;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class LocationAfterSave implements ObserverInterface
{

    protected $locationSourceRepository;

    protected $locationSourceFactory;

    public function __construct(
        LocationSourceRepositoryInterface $locationSourceRepository,
        LocationSourceFactory $locationSourceFactory,
    ) {
        $this->locationSourceRepository = $locationSourceRepository;
        $this->locationSourceFactory = $locationSourceFactory;
    }

    public function execute(EventObserver $observer)
    {
        $locationData = $observer->getData('location_data');
        $locationId = (int)$observer->getData('location_id');
        $locationSource = $this->locationSourceFactory->create();
        $locationSource->setLocationId((int)$locationId);
        $locationSource->setData('holiday_hours', json_encode($locationData['holiday_hours_container']));
        $this->locationSourceRepository->save($locationSource);
    }
}
