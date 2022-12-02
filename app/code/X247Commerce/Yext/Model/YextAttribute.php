<?php
namespace X247Commerce\Yext\Model;

use Amasty\Storelocator\Model\ResourceModel\Location\CollectionFactory;
use Amasty\Storelocator\Model\LocationFactory;
use Amasty\Storelocator\Model\AttributeFactory;
use Psr\Log\LoggerInterface;
use X247Commerce\Yext\Helper\YextHelper;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;
use Amasty\Storelocator\Model\ImageProcessor;
use Amasty\Storelocator\Model\Schedule;
use Amasty\Base\Model\Serializer;
use Magento\User\Model\UserFactory;
use X247Commerce\StoreLocatorSource\Helper\User;
use Magento\InventoryApi\Api\Data\SourceInterface;
use X247Commerce\StoreLocatorSource\Model\AdminSource;
use Magento\Framework\Event\ManagerInterface as EventManager;
use X247Commerce\StoreLocatorSource\Model\ResourceModel\LocatorSourceResolver;

class YextAttribute
{
    protected const AMASTY_AMLOCATOR_STORE_ATTRIBUTE = 'amasty_amlocator_store_attribute';
    protected const AMASTY_AMLOCATOR_STORE_HOLIDAY_HOURS = 'amasty_amlocator_holiday_hours';
    protected CollectionFactory $locationCollectionFactory;
    protected LocationFactory $locationFactory;
    protected AttributeFactory $attributeFactory;
    protected LoggerInterface $logger;
    protected YextHelper $yextHelper;
    protected ResourceConnection $resource;
    protected $connection;
    protected DirectoryList $directoryList;
    protected File $file;
    protected ImageProcessor $imageProcessor;
    protected Schedule $scheduleModel;
    protected Serializer $serializer;
    protected UserFactory $userFactory;
    protected User $userHelper;
    protected SourceInterface $sourceInterface;
    protected AdminSource $adminSource;
    private EventManager $eventManager;
    protected LocatorSourceResolver $locatorSourceResolver;

    public function __construct(
        CollectionFactory $locationCollectionFactory,
        LocationFactory $locationFactory,
        AttributeFactory $attributeFactory,
        LoggerInterface $logger,
        YextHelper $yextHelper, 
        ResourceConnection $resource,
        DirectoryList $directoryList,
        File $file,
        ImageProcessor $imageProcessor,
        Schedule $scheduleModel,
        Serializer $serializer,
        UserFactory $userFactory,
        User $userHelper,
        SourceInterface $sourceInterface,
        AdminSource $adminSource,
        EventManager $eventManager,
        LocatorSourceResolver $locatorSourceResolver
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
        $this->userFactory = $userFactory;
        $this->userHelper = $userHelper;
        $this->sourceInterface = $sourceInterface;
        $this->adminSource = $adminSource;
        $this->eventManager = $eventManager;
        $this->locatorSourceResolver = $locatorSourceResolver;
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
     * Get $attributeName id in table amasty_amlocator_attribute
     *
     * @return int|null
     */
    public function getIdOfAttribute($attributeName)
    {
        //get id of attribute $attributeName in table amasty_amlocator_attribute
        return $this->attributeFactory->create()->load($attributeName, 'attribute_code')->getAttributeId();
    }

    /**
     * Get attribute value by Location 
     *
     * @param \Amasty\Storelocator\Model\Location, attribute_code String
     * 
     * @return string||null
     */
    public function getAttributeValueByLocation($location, $attributeCode)
    {
        //get id of attribute in table
        $attributeId = $this->getIdOfAttribute($attributeCode);
        $tableName = $this->resource->getTableName(self::AMASTY_AMLOCATOR_STORE_ATTRIBUTE);
        $select = $this->connection->select()->from($tableName, ['value'])->where('store_id = ?', (int) $location->getId())->where('attribute_id = ?', $attributeId);

        $data = $this->connection->fetchOne($select);

        return $data;
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
                if ($this->yextHelper->getDeleteAdminSyncSetting()) {
                    try {                        
                        $users = $this->locatorSourceResolver->getUserByAmLocatorStore($location->getId());
                        if ($users) {
                            foreach ($users as $userId)
                            {
                                $user = $this->userFactory->create()->load($userId);
                                if ($user->getId()) {
                                    $user->delete();
                                }                        
                            }
                        }
                    } catch (\Exception $e) {
                        $this->logger->error($e->getMessage());
                    }
                }
                if ($this->yextHelper->getDeleteSourceSyncSetting()) {
                    try {
                        $sources = $this->locatorSourceResolver->getSourceCodeByAmLocator($location->getId());
                        foreach ($sources as $code)
                        {                       
                            $source = $this->sourceInterface->load($code);
                            if ($source->getSourceCode()) {
                                $source->delete();
                            } 
                        }                        
                    } catch (\Exception $e) {
                        $this->logger->error($e->getMessage());
                    }
                }
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
                    
                    $this->eventManager->dispatch('yext_webhook_location_add_after', [
                        'location' => $locationModel, 
                        'yext_data' => $data

                    ]);

                    $newUser = $this->editAdminUser($insert, $locationModel->getId());
                    $newSource = $this->editSource($insert, $locationModel->getId());

                    if (!is_null($newUser) && !is_null($newSource)) {
                        $this->adminSource->setData(['user_id' => $newUser->getUserId(), 'source_code' => $newSource->getSourceCode()]);
                        $this->adminSource->save();
                    }
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

                // $this->adminSource->setData(['user_id' => $newUser->getUserId(), 'source_code' => $source->getSourceCode()]);
                $this->editAdminUser($insert, $location->getId());
                
                if (isset($data['primaryProfile']['hours']['holidayHours'])) {
                    $this->editLocationHolidayHours($location, $data['primaryProfile']['hours']['holidayHours']);
                }
                // $this->adminSource->save();
                $location->save();
                if ($location->getId()) {
                   $this->insertYextEntityIdValue([$location->getId() => $yextEntityId]);
                   if (isset($data['primaryProfile']['hours']['reopenDate'])) {                       
                    $this->insertAttributeValue($location->getId(), 'temporarily_closed', 'Reopen Date: '.$data['primaryProfile']['hours']['reopenDate']);
                    var_dump($data['primaryProfile']['hours']['reopenDate']);
                   } else {
                    $this->insertAttributeValue($location->getId(), 'temporarily_closed');
                   }
                }
                return $location;
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Edit or Create Admin User
     * 
     * @param $userId int||null, $data array
     * 
     * @return \Magento\User\Model\UserFactory
     */
    public function editAdminUser($data, $locationId, $userId = null, $locationMail = null)
    {
        $adminInfo = [
            'username'  => $data['name'] ? strtolower(str_replace(' ', '_', trim($data['name']))) : 'cakebox',
            'firstname' => $data['name'] ? $data['name'] : 'Cakebox',
            'lastname'    => $data['name'] ? $data['name'] : 'Cakebox',
            'email'     => $data['email'] ? $data['email'] : strtolower(str_replace(' ', '_', trim($data['name']))).'@eggfreecake.co.uk',
            'interface_locale' => 'en_US',
            'is_active' => 1
        ];
        
        try {
            if (!$userId) {
                if ($data['email']) {
                    $userModel = $this->userFactory->create();
                    $user = $userModel->load($data['email'], 'email');
                    if ($user->getUserId()) {
                        return $user;
                    } else {
                        $this->logger->log('600', "Add New User");
                        $adminInfo['password']  = 'Cakebox123'.$this->yextHelper->randomString(10);
                        $this->logger->log('600', print_r($adminInfo, true));
                        $userModel->setData($adminInfo);
                        $userModel->setRoleId($this->userHelper->getStaffRole());
                    
                        $this->yextHelper->sendEmail($adminInfo['username'], $adminInfo['password'], $data['email']); 
                        $sources = $this->locatorSourceResolver->getSourceCodeByAmLocator($locationId);
                        $user = $userModel->save();
                        if ($sources) {
                            foreach ($sources as $source)
                            {
                                $this->adminSource->setData(['user_id' => $user->getUserId(), 'source_code' => $source]);
                                $this->adminSource->save();                                
                            }
                        }
                        return $user; 
                    }
                }            
            } 
            // else {
            //     $userModel = $this->userFactory->create()->load($userId);
            //     if ($userModel->getUsername() == $adminInfo['username'] && $userModel->getEmail() == $adminInfo['email']) {
            //         $this->logger->log('600', "Not Edit User");
            //         try {
                        
            //             $this->yextHelper->sendEmail($adminInfo['username'], $userModel->getPassword(), $adminInfo['email']);                        
            //         } catch (\Exception $e) {
            //             $this->logger->error($e->getMessage());
            //         }
            //         return $userModel;
            //     } else {
            //         $userModel->addData($adminInfo);
            //         $this->logger->log('600', "Edit User");
            //         try {
                        
            //             $this->yextHelper->sendEmail($adminInfo['username'], $userModel->getPassword(), $adminInfo['email']);                        
            //         } catch (\Exception $e) {
            //             $this->logger->error($e->getMessage());
            //         }
            //         return $userModel->save();
            //     }             
            // }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
    /**
     * Edit or Create Inventory Source
     * 
     * @param $userId int||null, $data array
     * 
     * @return \Magento\Inventory\Model\Source
     */
    public function editSource($storeData, $locationId, $sourceCode = null)
    {
       
        $sourceData = [
            'source_code' => strtolower(str_replace(' ', '_', trim($storeData['name']))),
            'name' => $storeData['name'],
            'enabled' => 1,
            'description' => $storeData['description'],
            'latitude' => $storeData['lat'],
            'longitude' => $storeData['lng'],
            'country_id' => $storeData['country'],
            'postcode' => $storeData['zip'],
            'amlocator_store' => $locationId
        ];
        try {
            if (!$sourceCode) {
                $source = $this->sourceInterface;
                $source->setData($sourceData);
                return $source->save();
                
            } else {
                $source = $this->sourceInterface->load($sourceCode);
                if ($source->getSourceCode()) {
                    return $source;
                } else {
                    $source->addData($sourceData);
                    return $source->save();
                }            
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
     * insert Attribute Value of store into table amasty_amlocator_store_attribute
     * 
     * @param $insert array
     * 
     * @return
     */
    public function insertAttributeValue($locationId, $attributeCode, $value = '')
    {
        //add value of attribute of location to table amasty_amlocator_store_attribute
        $attributeId = $this->getIdOfAttribute($attributeCode);
        $data = [];      
        $data['attribute_id'] = $attributeId;
        $data['store_id'] = $locationId;
        $data['value'] = $value;
        var_dump($data);
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
    {
        //download location photo gallery from Yext to local server
        /** @var string $tmpDir */
        $tmpDir = $this->getAmastyMediaDir();
        $uploadDir = $tmpDir . '/' . $locationId;
        /** create folder if it is not exists */
        $this->file->checkAndCreateFolder($tmpDir);

        $arr = explode('/', $imageUrl);
        
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
     * Edit or Add new \Amasty\Storelocator\Model\Schedule
     * 
     * @param $location Location, $openHoursfromYext Location's Open Time from Yext
     * 
     * @return \Amasty\Storelocator\Model\Schedule
     */
    public function editLocationSchedule($location, $openHoursfromYext)
    {
        $locationSchedule = $this->yextHelper->convertSchedule($openHoursfromYext);
        $schedule = $this->scheduleModel->load($location->getSchedule());
        
        if ($schedule) {            
            if (is_array($locationSchedule)) {
                $schedule->setSchedule($this->serializer->serialize($locationSchedule));
            }            
            $schedule->setName($location->getName() . " Schedule");
            
            return $schedule->save();
        } else {            
            if (is_array($locationSchedule)) {
                $this->scheduleModel->setSchedule($this->serializer->serialize($locationSchedule));
            }
            $this->scheduleModel->setName($location->getName() . " Schedule");
            return $this->scheduleModel->save();
        }
    }
    /**
     * Edit or Add new AmLocator Holiday Hours
     * 
     * @param $location Location, $holidayHoursfromYext Location's Open Time from Yext
     * 
     * @return LocationHolidayHours
     */
    public function editLocationHolidayHours($location, $holidayHoursfromYexts)
    {        
        $tableName = $this->resource->getTableName(self::AMASTY_AMLOCATOR_STORE_HOLIDAY_HOURS);
        $this->connection->delete($tableName, ['store_id = ?' => $location->getId()]);
        $insertData = [];
        $openTime = '00:00';
        $breakStart = '00:00';
        $breakEnd = '00:00';
        $endTime = '00:00';

        try {
            foreach ($holidayHoursfromYexts as $holidayHoursfromYext)
            {
                if (!isset($holidayHoursfromYext['isRegularHours']) && !isset($holidayHoursfromYext['isClosed'])) {
                    $openTime = isset($holidayHoursfromYext['openIntervals'][0]["start"]) ? $holidayHoursfromYext['openIntervals'][0]["start"] : '00:00';
                    $breakStart = isset($holidayHoursfromYext['openIntervals'][1]["start"]) ? $holidayHoursfromYext['openIntervals'][0]["end"] : '00:00';
                    $breakEnd = isset($holidayHoursfromYext['openIntervals'][1]["start"]) ? $holidayHoursfromYext['openIntervals'][1]["start"] : '00:00';
                    $endTime = isset($holidayHoursfromYext['openIntervals'][1]["end"]) ? $holidayHoursfromYext['openIntervals'][1]["end"] : $holidayHoursfromYext['openIntervals'][0]["end"];
                    $insertData[] = ['store_id' => $location->getId(), 'type' => 'Holiday Hours', 'store_name' => $location->getName(), 'date' => $holidayHoursfromYext['date'], 'open_time' => $openTime, 'break_start' => $breakStart, 'break_end' => $breakEnd, 'close_time' => $endTime];
                }
                if (isset($holidayHoursfromYext['isClosed'])) {
                   $insertData[] = ['store_id' => $location->getId(), 'type' => 'Closed', 'store_name' => $location->getName(), 'date' => $holidayHoursfromYext['date'], 'open_time' => $openTime, 'break_start' => $breakStart, 'break_end' => $breakEnd, 'close_time' => $endTime];
                }
                if (isset($holidayHoursfromYext['isRegularHours'])) {
                   $insertData[] = ['store_id' => $location->getId(), 'type' => 'Regular Hours', 'store_name' => $location->getName(), 'date' => $holidayHoursfromYext['date'], 'open_time' => $openTime, 'break_start' => $breakStart, 'break_end' => $breakEnd, 'close_time' => $endTime];
                }
            }
            var_dump($insertData);
            return $this->connection->insertOnDuplicate($tableName, $insertData, ['open_time', 'break_start', 'break_end', 'close_time']);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}