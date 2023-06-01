<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace X247Commerce\HolidayOpeningTime\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use X247Commerce\Yext\Helper\YextHelper;
use Magento\Framework\App\ResourceConnection;
use X247Commerce\HolidayOpeningTime\Model\Source\HolidayHour\Type;

class SyncHoliday implements ObserverInterface
{   
    
    public const STORE_LOCATION_HOLIDAY_TABLE = 'store_location_holiday';

    protected LoggerInterface $logger;
    protected YextHelper $yextHelper;
    protected ResourceConnection $resource;
    protected $connection;

    public function __construct(
        LoggerInterface $logger,
        YextHelper $yextHelper, 
        ResourceConnection $resource
    ) {
        $this->logger = $logger;
        $this->yextHelper = $yextHelper;
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
    }

    /**
     * Address after save event handler
     *
     * @param Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(Observer $observer)
    {   
        try {
            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/sync_holiday.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);
            
            $location = $observer->getLocation();
            $data = $observer->getYextData();
            // $logger->info(print_r($data, true));

            $primaryData = $data['primaryProfile'];
            $holidayAction = [];


            if (!empty($primaryData['c_holiday_action'])) {
                $holidayAction = $primaryData['c_holiday_action'];
            }
            $this->saveHolidayAction($location, $holidayAction);

            if (!empty($primaryData['hours']) && 
                !empty($primaryData['hours']['holidayHours'])) {
                $this->editLocationHolidayHours($location, $primaryData['hours'], $holidayAction);
            }
            
            
        } catch (\Exception $e) {
            $logger->info('There is an error when save holiday: '.$e->getMessage());
        }
        
    

    }


     /**
     * Address after save event handler
     *
     * @param Location $location
     * @param array $holidayAction
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function saveHolidayAction($location, $holidayAction) 
    {
        try {
            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/sync_holiday.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);

            $noOfferDeliveryPickup = [];
            if (!empty($holidayAction)) {
                if (in_array('DO_NOT_OFFER_DELIVERY', $holidayAction)) {
                    array_push($noOfferDeliveryPickup, 1);
                }
                if (in_array('DO_NOT_OFFER_PICKUP', $holidayAction)) {
                    array_push($noOfferDeliveryPickup, 2);
                }
            } 
            $location->setData('holiday_action', implode(',', $noOfferDeliveryPickup))->save();
        } catch (\Exception $e) {
            $logger->info('There is an error when save location holiday action: '.$e->getMessage());
            
        }
    }

    /**
     * Edit or Add new AmLocator Holiday Hours
     * 
     * @param $location Location, $holidayHoursfromYext Location's Open Time from Yext
     * 
     * @return LocationHolidayHours
     */
    public function editLocationHolidayHours($location, $hoursData)
    {        
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/sync_holiday.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);

        $holidayHoursfromYexts = $hoursData['holidayHours'];

        $tableName = $this->resource->getTableName(self::STORE_LOCATION_HOLIDAY_TABLE);
        $holidayAction = $location->getData('holiday_action');
// 
        // $logger->info(print_r($hoursData,true));
        // $logger->info(print_r($holidayAction,true));
        $holidayAction = explode(',', $holidayAction);
        $disableDelivery = in_array(1, $holidayAction) ? 1 : 0;
        $disablePickup   = in_array(2, $holidayAction) ? 1 : 0;

        try {
            foreach ($holidayHoursfromYexts as $holidayHoursfromYext)
            {
                $insertData = [];
                $openTime = '00:00';
                $endTime = '00:00';

                if (isset($holidayHoursfromYext['isRegularHours'])) {
                    $holidayDate = $holidayHoursfromYext['date'];
                    $dateInWeek = strtolower((date_create($holidayDate))->format('l'));
                    if (empty($hoursData[$dateInWeek]['isClosed'])) {
                        $openTime = $hoursData[$dateInWeek]['openIntervals'][0]['start'];
                        $closedTime = $hoursData[$dateInWeek]['openIntervals'][0]['end'];
                    }
                    
                    $insertData = [    
                        'title' => 'Holiday',
                        'date' => $holidayDate, 
                        'open_time' => $openTime, 
                        'closed_time' => $endTime,
                        'disable_delivery' => $disableDelivery,
                        'disable_pickup' => $disablePickup,
                        'store_location_id' => $location->getId(),
                        'type' => Type::OPEN_VALUE,
                    ];
                }

                if (isset($holidayHoursfromYext['isClosed'])) {
                   $insertData = [
                        'title' => 'Holiday',
                        'date' => $holidayHoursfromYext['date'], 
                        'open_time' => $openTime, 
                        'closed_time' => $endTime,
                        'disable_delivery' => $disableDelivery,
                        'disable_pickup' => $disablePickup,
                        'store_location_id' => $location->getId(), 
                        'type' => Type::CLOSED_VALUE
                    ];

                }

                if (!isset($holidayHoursfromYext['isRegularHours']) && !isset($holidayHoursfromYext['isClosed'])) {
                    $openTime = $holidayHoursfromYext['openIntervals'][0]['start'] ?: '00:00';
                    $endTime  = $holidayHoursfromYext['openIntervals'][0]['end']  ?: '00:00';
                    $insertData = [
                        'title' => 'Holiday',
                        'date' => $holidayHoursfromYext['date'], 
                        'open_time' => $openTime, 
                        'closed_time' => $endTime,
                        'disable_delivery' => $disableDelivery,
                        'disable_pickup' => $disablePickup,
                        'store_location_id' => $location->getId(), 
                        'type' => Type::OPEN_VALUE, 
                    ];
                }
                try {
                    $this->connection->insertOnDuplicate($tableName, [$insertData]);
                } catch (\Exception $e) {
                    $logger->info('Cannot insert holiday data: ' . print_r($insertData, true));
                }
            }
        } catch (\Exception $e) {
            $logger->info('We cannot process holiday data'.$e->getMessage());
        }
    }

}
