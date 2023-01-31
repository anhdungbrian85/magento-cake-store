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
use Amasty\Storelocator\Model\ScheduleFactory;
use Amasty\Base\Model\Serializer;
use Magento\User\Model\UserFactory;
use X247Commerce\StoreLocatorSource\Helper\User;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\Data\SourceInterfaceFactory;
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
    protected ScheduleFactory $scheduleModel;
    protected Serializer $serializer;
    protected UserFactory $userFactory;
    protected User $userHelper;
    protected SourceInterface $sourceInterface;
    protected SourceInterfaceFactory $sourceInterfaceFactory;
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
        ScheduleFactory $scheduleModel,
        Serializer $serializer,
        UserFactory $userFactory,
        User $userHelper,
        SourceInterface $sourceInterface,
        SourceInterfaceFactory $sourceInterfaceFactory,
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
        $this->sourceInterfaceFactory = $sourceInterfaceFactory;
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
        if (isset($input['geocodedCoordinate'])) {
            $data['lat'] = isset($input['geocodedCoordinate']['latitude']) ? $input['geocodedCoordinate']['latitude'] : 0 ;
            $data['lng'] = isset($input['geocodedCoordinate']['longitude']) ? $input['geocodedCoordinate']['longitude'] : 0 ;
        }
        if (!isset($input['geocodedCoordinate']) && isset($input['yextDisplayCoordinate'])) {
            $data['lat'] = isset($input['yextDisplayCoordinate']['latitude']) ? $input['yextDisplayCoordinate']['latitude'] : 0 ;
            $data['lng'] = isset($input['yextDisplayCoordinate']['longitude']) ? $input['yextDisplayCoordinate']['longitude'] : 0 ;
        }
        if (!isset($input['geocodedCoordinate']) && !isset($input['yextDisplayCoordinate']) && isset($input['cityCoordinate'])) {
            $data['lat'] = isset($input['cityCoordinate']['latitude']) ? $input['cityCoordinate']['latitude'] : 0 ;
            $data['lng'] = isset($input['cityCoordinate']['longitude']) ? $input['cityCoordinate']['longitude'] : 0 ;
        }
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
                        $sourceCodeByAmLocator = $this->locatorSourceResolver->getSourceCodeByAmLocator($location->getId());
                        if (!empty($sourceCodeByAmLocator))
                        {                       
                            $source = $this->sourceInterface->load($sourceCodeByAmLocator);
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
                    // We delay inserting admin and source to 2nd save.

                    // $newUser = $this->editAdminUser($insert, $locationModel->getId());
                    // $newSource = $this->editSource($insert, $locationModel->getId());

                    // $defaultAssignStockId = $this->yextHelper->getDefaultAssignStock();

                    // if (!is_null($newSource)) {
                    //     $this->assignSourceToStock($newSource->getSourceCode(), $defaultAssignStockId, $locationModel->getId());
                    // }

                    // if (!is_null($newUser) && !is_null($newSource)) {
                    //     $this->adminSource->setData(['user_id' => $newUser->getUserId(), 'source_code' => $newSource->getSourceCode()]);
                    //     $this->adminSource->save();
                    //     $this->locatorSourceResolver->assignAmLocatorStoreToSource($locationModel->getId(), $newSource->getSourceCode());
                    // }
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
     * @return \Amasty\Storelocator\Model\Location|null
     */
    public function editLocation($data, $yextEntityId)
    {
        // $this->logger->log('600', print_r($data, true));
        try {
            
            $insert = $this->responseDataProcess($data['primaryProfile']);
            $location = $this->getLocationByYext("'$yextEntityId'");

            if (!$location->getId()) {
                return null;
            } else {
                //edit location
                $location->addData($insert);
                
                if (isset($data['primaryProfile']['hours']['holidayHours'])) {
                    $this->editLocationHolidayHours($location, $data['primaryProfile']['hours']['holidayHours']);
                }
                $location->save();

                $notAsdaFlag = empty($data['primaryProfile']['asda_parent_store']);

                if ($notAsdaFlag) {
                    $adminUser = $this->editAdminUser($insert, $location->getId());
                    $storeSource = $this->editSource($insert, $location->getId());
                    if (!empty($adminUser)) {
                        $adminUserId = $adminUser->getUserId();
                    }
                }   else {
                    $parentLocationYextEntity = $data['primaryProfile']['asda_parent_store'][0];
                    $parentLocation = $this->getLocationByYext("'$parentLocationYextEntity'");
                    $sourceCode = $this->locatorSourceResolver->getSourceCodeByAmLocator($parentLocation->getId());
                    $storeSource = $this->sourceInterfaceFactory->create()->load($sourceCode);
                    $adminUser = $this->locatorSourceResolver->getUserBySource($sourceCode);
                    if (count($adminUser)) {
                        $adminUserId = $adminUser[0];
                    }

                }
                $defaultAssignStockId = $this->yextHelper->getDefaultAssignStock();

                if (!empty($storeSource) && $notAsdaFlag) {
                    $this->assignSourceToStock($storeSource->getSourceCode(), $defaultAssignStockId, $location->getId());
                }

                if (!empty($adminUserId) && !empty($storeSource)) {
                    $this->locatorSourceResolver->assignUserToSource($adminUserId, $storeSource->getSourceCode());
                    $this->locatorSourceResolver->assignAmLocatorStoreToSource($location->getId(), $storeSource->getSourceCode());
                }
                if (!$notAsdaFlag) {
                    $this->locatorSourceResolver->assignAsdaAmLocatorStoreToParent(
                        $parentLocation->getId(), $location->getId()
                    );
                }
                if (isset($data['primaryProfile']['hours']['reopenDate'])) {                       
                    $this->insertAttributeValue($location->getId(), 'temporarily_closed', 'Reopen Date: '.$data['primaryProfile']['hours']['reopenDate']);
                } else {
                    $this->insertAttributeValue($location->getId(), 'temporarily_closed');
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
    public function editAdminUser($data, $locationId)
    {
        try {

            $adminInfo = [
                'username'  => $data['name'] ? strtolower(str_replace([' ', '(', ')'], ['_', '', ''], trim($data['name']))) : 'cakebox',
                'firstname' => 'Cake Box',
                'lastname'    => $data['name'] ? str_replace(['Cake Box ', '(', ')'], '', trim($data['name'])) : 'Cake Box',
                'email'     => $data['email'] ? $data['email'] : strtolower(str_replace(['cake box', ' ', '(', ')'], '_', trim($data['name']))).'@eggfreecake.co.uk',
                'interface_locale' => 'en_US',
                'is_active' => 1,
                'password'  => 'Cakebox123'
            ];
            $userId = $this->locatorSourceResolver->getUserByAmLocatorStore($locationId);
            if (!empty($userId)) {
                $userId = $userId[0];
                $user = $this->userFactory->create()->load($userId);
                if ($adminInfo['email'] != $user->getEmail()) {
                    //only edit when email was changed
                    $user->addData($adminInfo)->save();
                }
            }   else {
                $user = $this->userFactory->create()->load($adminInfo['email'], 'email');
                if (!$user->getId()) {
                    $user = $this->userFactory->create();
                    $user->addData($adminInfo)->save();
                }
            }

            if (!empty($data['email'])) {
                $this->yextHelper->sendEmail($adminInfo['username'], $adminInfo['password'], $data['email']);
            }   else {
                $this->logger->log('600', "Add New User without Yext Store Email");
            }
            
            return $user;
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
    public function editSource($storeData, $locationId)
    {   
        $locationName = trim($storeData['name']);

        $sourceData = [
            'source_code' => strtolower(str_replace([' ', '(', ')', '(ASDA)'], ['_', '', '', ''], $locationName)),
            'name' => $storeData['name'],
            'enabled' => 1,
            'description' => empty($storeData['description']) ? '' : $storeData['description'],
            'latitude' => empty($storeData['lat']) ? '' : $storeData['lat'],
            'longitude' => empty($storeData['lng']) ? '' : $storeData['lng'],
            'country_id' => empty($storeData['country']) ? '' : $storeData['country'],
            'postcode' => empty($storeData['zip']) ? '' : $storeData['zip'],
            'amlocator_store' => $locationId
        ];

        try {
            $source = $this->sourceInterfaceFactory->create()->load($sourceData['source_code']);
            if (!$source->getSourceCode()) {
                $source = $this->sourceInterfaceFactory->create();
                $source->setData($sourceData)->save();
                return $source;
            }
            
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Assign source to a stock
     *
     * @return void
     */
    public function assignSourceToStock($sourceCode, $stockId, $priority = 0)
    {
        $connection = $this->resource->getConnection();
        $stockSourceLinkData = [[
            'source_code' => $sourceCode,
            'stock_id' => $stockId,
            'priority' => $priority,
        ]];
        $connection->insertOnDuplicate($this->resource->getTableName('inventory_source_stock_link'), $stockSourceLinkData);
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
        
        if ($location->getSchedule()) {
            
            $schedule = $this->scheduleModel->create()->load($location->getSchedule());
            if (is_array($locationSchedule)) {
                $schedule->setSchedule($this->serializer->serialize($locationSchedule));
            }            
            $schedule->setName($location->getName() . " Schedule");
            
            return $schedule->save();
        } else {

            if (is_array($locationSchedule)) {
                $newSchedule = $this->scheduleModel->create();
                $newSchedule->setSchedule($this->serializer->serialize($locationSchedule));
            }
            $newSchedule->setName($location->getName() . " Schedule");
            return $newSchedule->save();
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
            // var_dump($insertData);
            return $this->connection->insertOnDuplicate($tableName, $insertData, ['open_time', 'break_start', 'break_end', 'close_time']);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
    /**
     * Assign Amasty Store Location To Source
     * @param AmLocation Id, Source Code
     * @return 
     * 
     **/
    public function assignAmLocatorStoreToSource($locationId, $sourceCode)
    {
        $this->locatorSourceResolver->assignAmLocatorStoreToSource($locationId, $sourceCode);
    }
    /**
     * Unassign Amasty Store Location To Source
     * @param AmLocation Id, Source Code
     * @return 
     * 
     **/
    public function unAssignAmLocatorStoreWithSource($locationId, $sourceCode)
    {
        $this->locatorSourceResolver->unAssignAmLocatorStoreWithSource($locationId, $sourceCode);
    }
}