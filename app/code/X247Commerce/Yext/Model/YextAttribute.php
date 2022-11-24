<?php
namespace X247Commerce\Yext\Model;

class YextAttribute
{
    protected const AMASTY_AMLOCATOR_STORE_ATTRIBUTE = 'amasty_amlocator_store_attribute';

    protected $locationCollectionFactory;

    protected $locationFactory;

    protected $attributeFactory;

    protected $logger;

    protected $yextHelper;

    protected $resource;

    protected $connection;

    protected $directoryList;
    
    protected $file;

    protected $imageProcessor;

    protected $scheduleModel;

    protected $serializer;

    public function __construct(
    \Amasty\Storelocator\Model\ResourceModel\Location\CollectionFactory $locationCollectionFactory,
    \Amasty\Storelocator\Model\LocationFactory $locationFactory,
    \Amasty\Storelocator\Model\AttributeFactory $attributeFactory,
    \Psr\Log\LoggerInterface $logger,
    \X247Commerce\Yext\Helper\YextHelper $yextHelper, 
    \Magento\Framework\App\ResourceConnection $resource,
    \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
    \Magento\Framework\Filesystem\Io\File $file,
    \Amasty\Storelocator\Model\ImageProcessor $imageProcessor,
    \Amasty\Storelocator\Model\Schedule $scheduleModel,
    \Amasty\Base\Model\Serializer $serializer
    ) {
        $this->locationCollectionFactory = $locationCollectionFactory;
        $this->locationFactory = $locationFactory;
        $this->attributeFactory = $attributeFactory;
        $this->logger = $logger;
        $this->yextHelper = $yextHelper;
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->directoryList = $directoryList;
        $this->file = $file;
        $this->imageProcessor = $imageProcessor;
        $this->scheduleModel = $scheduleModel;
        $this->serializer = $serializer;
    }

    /**
     * Get yext_entity_id attribute id in table amasty_amlocator_attribute
     *
     * @return int|null
     */
    public function getYextEntityAttributeId()
    {
        //get id of attribute yext_entity_id in table amasty_amlocator_attribute
        return $this->attributeFactory->create()->load('yext_entity_id', 'attribute_code')->getAttributeId();
    }

    /**
     * Get Location by yext_entity_id value
     *
     * @param value of yext_entity_id
     * @return \Amasty\Storelocator\Model\Location ||null
     */
    public function getLocationByYext($value)
    {
        //get amasty_amlocator_location by attribute yext_entity_id
        $yextAttributeId = $this->getYextEntityAttributeId();
        $attributesData = [$yextAttributeId => [$value]];

        $locations = $this->locationCollectionFactory->create();
        $location = $locations->applyAttributeFilters($attributesData)->getFirstItem();

        return $location;
    }

    /**
     * Get yext_entity_id value by Location 
     *
     * @param \Amasty\Storelocator\Model\Location
     * 
     * @return string||null
     */
    public function getYextEntityIdByLocation($location)
    {
        //get value of yext_entity_id by location

        //get id of attribute yext_entity_id in table
        $yextAttributeId = $this->getYextEntityAttributeId();
        $tableName = $this->resource->getTableName(self::AMASTY_AMLOCATOR_STORE_ATTRIBUTE);
        $select = $this->connection->select()->from($tableName, ['value'])->where('store_id = ?', (int) $location->getId())->where('attribute_id = ?', $yextAttributeId);

        $data = $this->connection->fetchOne($select);

        return $data;
    }

    /**
     * Get all yext_entity_id values in table amasty_amlocator_store_attribute
     * 
     * @return array||null
     */
    public function getAllYextEntityIdValue()
    {
        //get all value of yext_entity_id

        //get id of attribute yext_entity_id in table
        $yextAttributeId = $this->getYextEntityAttributeId();
        $tableName = $this->resource->getTableName(self::AMASTY_AMLOCATOR_STORE_ATTRIBUTE);
        $select = $this->connection->select()->from($tableName, ['store_id', 'value'])->where('attribute_id = ?', $yextAttributeId);

        $data = $this->connection->fetchAll($select);

        return $data;
    }

    /**
     * Process data from event or response to data of \Amasty\Storelocator\Model\Location
     *
     * @param array $input
     * 
     * @return array
     */
    public function responseDataProcess($input)
    {
        //process data from $input to insert into amasty_amlocator_location
        $data = [];
        $data['name'] = isset($input['name']) ? $input['name'] : '';
        $url_key = '';
        if (isset($input['name'])) {
            $url_key = $this->yextHelper->getUrlKeyFromName($input['name']);
        }
        $data['url_key'] = $url_key;
        $data['country'] = isset($input['address']['countryCode']) ? $input['address']['countryCode'] : '' ;
        $data['status'] = 1;
        $data['stores'] = 0;
        $address = '';
        if (isset($input['address']['line1']) && isset($input['address']['line2'])) {
            $address = $input['address']['line1'] . ' ' . $input['address']['line2'];
        }
        if (isset($input['address']['line1']) && !isset($input['address']['line2'])) {
            $address = $input['address']['line1'];
        }
        $data['address'] = $address;
        $data['city'] = isset($input['address']['city']) ? $input['address']['city'] : '';
        $data['zip'] = isset($input['address']['postalCode']) ? $input['address']['postalCode'] : '';
        $data['state'] = isset($input['address']['region']) ? $input['address']['region'] : '' ;
        $data['lat'] = isset($input['cityCoordinate']['latitude']) ? $input['cityCoordinate']['latitude'] : '' ;
        $data['lng'] = isset($input['cityCoordinate']['longitude']) ? $input['cityCoordinate']['longitude'] : '' ;
        $data['description'] = isset($input['description']) ? $input['description'] : '' ;
        $data['phone'] = isset($input['mainPhone']) ? $input['mainPhone'] : '' ;
        $data['email'] = isset($input['emails'][0]) ? $input['emails'][0] : '' ;
        $data['website'] = isset($input['facebookPageUrl']) ? $input['facebookPageUrl'] : '' ;
        // $data['actions_serialized'] = isset($input['primaryProfile']) ? $input['primaryProfile'] : '' ;
        $data['photoGallery'] = [];
        if (isset($input["photoGallery"])) {
            foreach ($input["photoGallery"] as $image) {
                $data['photoGallery'][] = $image["image"]["url"];
            }
        }

        return $data;
    }

    /**
     * Delete \Amasty\Storelocator\Model\Location base on yext_entity_id value
     *
     * @param $yextEntityId: (string) yext_entity_id value
     * 
     */
    public function deleteLocation($yextEntityId)
    {
        try {
            $location = $this->getLocationByYext($yextEntityId);
            if ($location) {
                $location->delete();
            }            
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Add new \Amasty\Storelocator\Model\Location
     * 
     * @param $data array from event or response, $yextEntityId: (string) yext_entity_id value
     * 
     * @return \Amasty\Storelocator\Model\Location
     */
    public function addLocation($data, $yextEntityId)
    {
        try {
            $insert = $this->responseDataProcess($data['primaryProfile']);
            // $this->logger->log('600', print_r($insert, true));
            $location = $this->getLocationByYext("'$yextEntityId'");
            if (!$location->getId()) {
                // add new location
                $locationModel = $this->locationFactory->create();
                $locationModel->setData($insert); 
                $locationModel->save();
                if ($locationModel->getId()) {
                   $this->insertYextEntityIdValue([$locationModel->getId() => $yextEntityId]);
                }
                return $locationModel;
            } else {
                //location exists
                return $location;
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Edit \Amasty\Storelocator\Model\Location
     * 
     * @param $data array from event or response, $yextEntityId: (string) yext_entity_id value
     * 
     * @return \Amasty\Storelocator\Model\Location
     */
    public function editLocation($data, $yextEntityId)
    {
        try {
            
            $insert = $this->responseDataProcess($data['primaryProfile']);
            
            $this->logger->log('600', print_r($data, true));
            $location = $this->getLocationByYext("'$yextEntityId'");
            if (!$location->getId()) {
                //location do not exist
                return '';
            } else {
                //edit location
                $location->addData($insert);
                $location->save();
                if ($location->getId()) {
                   $this->insertYextEntityIdValue([$location->getId() => $yextEntityId]);
                }
                return $location;
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Link location with yext_entity_id in table amasty_amlocator_store_attribute
     * 
     * @param $insert array
     * 
     * @return
     */
    public function insertYextEntityIdValue($insert)
    {
        //add value of attribute yext_entity_id to location in table amasty_amlocator_store_attribute
        $yextAttribute = $this->getYextEntityAttributeId();
        $data = [];
        foreach ($insert as $key => $value) {        
          $data[] = [
            'attribute_id' => $yextAttribute,
            'store_id' => $key,
            'value' => $value
          ];
        }
        try {
            $tableName = $this->resource->getTableName(self::AMASTY_AMLOCATOR_STORE_ATTRIBUTE);
            
            $this->connection->insertOnDuplicate($tableName, $data, ['value']);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Download location photo gallery from Yext to local server when sync data
     * 
     * @param $imageUrl (string) Image Url, $locationId: id of Location
     * 
     * @return $name: name of downloaded image || false
     */
    public function downloadLocationImageToLocal($imageUrl, $locationId)
    {//download location photo gallery from Yext to local server
        /** @var string $tmpDir */
        $tmpDir = $this->getAmastyMediaDir();
        $uploadDir = $tmpDir . '/' . $locationId;
        /** create folder if it is not exists */
        $this->file->checkAndCreateFolder($tmpDir);

        $arr = explode('/', $imageUrl);
        // var_dump($arr);
        $name = implode('-',array_slice($arr, 4, 5));
        /** @var string $newFileName */
        $newFileName = $uploadDir . '/' . $name;
        /** read file from URL and copy it to the new destination */
        $result = $this->file->read($imageUrl, $newFileName);
        
        if ($result) {
            return $name;
        } else {
            return false;
        }
        
    }

    /**
     * Get download location
     * 
     * @param 
     * 
     * @return string
     */
    protected function getAmastyMediaDir()
    {
        return $this->directoryList->getPath($this->directoryList::MEDIA) . DIRECTORY_SEPARATOR . 'amasty/amlocator/gallery';
    }

    /**
     * Convert from Yext Open Hours to Amasty Schedule
     * 
     * @param $yextSchedule array
     * 
     * @return array
     */
    public function convertSchedule($yextSchedule)
    {
        // $yextSchedule = json_decode($yextHours);

        $amastySchedule = [];

        $amastySchedule =   [   
                                "monday" => $this->convertWeekDay($yextSchedule, "monday"),
                                "tuesday" => $this->convertWeekDay($yextSchedule, "tuesday"),
                                "wednesday" => $this->convertWeekDay($yextSchedule, "wednesday"),
                                "thursday" => $this->convertWeekDay($yextSchedule, "thursday"),
                                "friday" => $this->convertWeekDay($yextSchedule, "friday"),
                                "saturday" => $this->convertWeekDay($yextSchedule, "saturday"),
                                "sunday" => $this->convertWeekDay($yextSchedule, "sunday")
                            ];

        return $amastySchedule;
    }

    /**
     * Convert from week day of Yext Open Hours to week day of Amasty Schedule
     * 
     * @param $yextSchedule array, $day week day
     * 
     * @return array
     */
    public function convertWeekDay($yextSchedule, $day) 
    {
        $amastySchedule = [];
        if (!$yextSchedule[$day]["isClosed"]) {
            $amastySchedule[$day."_status"] = 1;
            $openTime = $yextSchedule[$day]["openIntervals"];
            if ($openTime) {
                $amastySchedule["from"]["hours"] = explode(':', $openTime[0]["start"])[0];
                $amastySchedule["from"]["minutes"] = explode(':', $openTime[0]["start"])[1];
                $amastySchedule["break_from"]["hours"] = isset($openTime[1]) ? explode(':', $openTime[0]["end"])[0] : "00";
                $amastySchedule["break_from"]["minutes"] = isset($openTime[1]) ? explode(':', $openTime[0]["end"])[1] : "00";
                $amastySchedule["break_to"]["hours"] = isset($openTime[1]) ? explode(':', $openTime[1]["start"])[0] : "00";
                $amastySchedule["break_to"]["minutes"] = isset($openTime[1]) ? explode(':', $openTime[1]["start"])[1] : "00";
                $amastySchedule["to"]["hours"] = isset($openTime[1]) ? explode(':', $openTime[1]["end"])[0] : explode(':', $openTime[0]["end"])[0];
                $amastySchedule["to"]["minutes"] = isset($openTime[1]) ? explode(':', $openTime[1]["end"])[1] : explode(':', $openTime[0]["end"])[1];
            }            
        } else {
            $amastySchedule[$day."_status"] = 0;
            $amastySchedule["from"]["hours"] = "00";
            $amastySchedule["from"]["minutes"] = "00";
            $amastySchedule["break_from"]["hours"] = "00";
            $amastySchedule["break_from"]["minutes"] = "00";
            $amastySchedule["break_to"]["hours"] = "00";
            $amastySchedule["break_to"]["minutes"] = "00";
            $amastySchedule["to"]["hours"] = "00";
            $amastySchedule["to"]["minutes"] = "00";
        }

        return $amastySchedule;
    }

    /**
     * Edit or Add new \Amasty\Storelocator\Model\Schedule
     * 
     * @param $location Location, $openHoursfromYext Location's Open Time from Yext
     * 
     * @return \Amasty\Storelocator\Model\Schedule
     */
    public function editLocationSchedule($location, $openHoursfromYext)
    {
        $locationSchedule = $this->convertSchedule($openHoursfromYext);
        $schedule = $this->scheduleModel->load($location->getSchedule());
        
        if ($schedule) {            
            if (is_array($locationSchedule)) {
                $schedule->setSchedule($this->serializer->serialize($locationSchedule));
            }            
            $schedule->setName($location->getName() . " Schedule");
            // var_dump($schedule->getData());die();
            return $schedule->save();
        } else {            
            if (is_array($locationSchedule)) {
                $this->scheduleModel->setSchedule($this->serializer->serialize($locationSchedule));
            }
            $this->scheduleModel->setName($location->getName() . " Schedule");
            return $this->scheduleModel->save();
        }
    }
}