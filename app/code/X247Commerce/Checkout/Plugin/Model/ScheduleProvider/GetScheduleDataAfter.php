<?php

namespace X247Commerce\Checkout\Plugin\Model\ScheduleProvider;

use Amasty\StorePickupWithLocator\Model\TimeHandler;
use Magento\Framework\Stdlib\ArrayManager;
use Amasty\Base\Model\Serializer;
use Amasty\Storelocator\Model\ResourceModel\Schedule\Collection;
use Amasty\Storelocator\Model\ResourceModel\Schedule\CollectionFactory;

class GetScheduleDataAfter
{

    protected $collectionFactory;

    protected $timeHandler;

    protected $serializer;

    public function __construct(
        CollectionFactory $collectionFactory,
        \X247Commerce\Checkout\Model\TimeHandler $timeHandler,
        Serializer $serializer
    ) {
        $this->timeHandler = $timeHandler;
        $this->collectionFactory = $collectionFactory;
        $this->serializer = $serializer;
    }

    public function afterGetScheduleDataArray(\Amasty\StorePickupWithLocator\Model\ScheduleProvider $subject, $result, $scheduleIds)
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('id', ['in' => $scheduleIds]);
        $timeIntervals = [
            'default' => $this->timeHandler->generate(
                \X247Commerce\Checkout\Model\TimeHandler::START_TIME,
                \X247Commerce\Checkout\Model\TimeHandler::END_TIME
            )
        ];
        foreach ($collection->getData() as &$scheduleData) {
            $schedule = $this->serializer->unserialize($scheduleData['schedule']);
            $timeIntervals[$scheduleData['id']] = $this->timeHandler->execute($schedule, 60);
        }
        $result['intervals'] = $timeIntervals;
        return $result;
    }
}
