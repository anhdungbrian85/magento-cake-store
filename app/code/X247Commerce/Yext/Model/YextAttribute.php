<?php
namespace X247Commerce\Yext\Model;

use Magento\InventoryApi\Api\Data\SourceInterface;
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

    protected $userFactory;

    protected $userHelper;

    protected $sourceInterface;

    protected $adminSource;

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
    \Amasty\Base\Model\Serializer $serializer,
    \Magento\User\Model\UserFactory $userFactory,
    \X247Commerce\StoreLocatorSource\Helper\User $userHelper,
    SourceInterface $sourceInterface,
    \X247Commerce\StoreLocatorSource\Model\AdminSource $adminSource
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
                if ($this->yextHelper->getDeleteAdminSyncSetting()) {
                    $user = $this->userFactory->create()->load($location->getYextUserId());
                    if ($user->getId()) {
                        $user->delete();
                    }
                }
                if ($this->yextHelper->getDeleteSourceSyncSetting()) {
                    $source = $this->sourceInterface->load($location->getYextSourceCode());
                    if ($source->getSourceCode()) {
                        $source->delete();
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
                    $newUser = $this->editAdminUser($insert, $locationModel->getId());
                    $newSource = $this->editSource($insert, $locationModel->getId());
                    $locationModel->setYextSourceCode($newSource->getSourceCode());
                    $locationModel->setYextUserId($newUser->getUserId());
                    $locationModel->save();
                    $this->adminSource->setData(['user_id' => $newUser->getUserId(), 'source_code' => $newSource->getSourceCode()]);
                    $this->adminSource->save();
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
                $yextUserId = $location->getYextUserId();
                $yextSourceCode = $location->getYextSourceCode();
                $newUser = $this->editAdminUser($insert, $location->getId(), $location->getYextUserId());
                $source = $this->editSource($insert, $location->getId(), $location->getYextSourceCode());
                $location->setYextUserId($newUser->getUserId());
                $location->setYextSourceCode($source->getSourceCode());
                if (!$yextUserId || !$yextSourceCode) {
                    $this->adminSource->setData(['user_id' => $newUser->getUserId(), 'source_code' => $source->getSourceCode()]);
                }
                $this->adminSource->save();
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
     * Edit or Create Admin User
     * 
     * @param $userId int||null, $data array
     * 
     * @return \Magento\User\Model\UserFactory
     */
    public function editAdminUser($data, $locationId, $userId = null)
    {
        $adminInfo = [
            'username'  => $data['name'] ? strtolower(str_replace(' ', '_', trim($data['name']))) : 'x247commerce'.date("Y.m.d.h.i.s"),
            'firstname' => $data['name'] ? $data['name'] : 'x247commerce'.date("Y.m.d.h.i.s"),
            'lastname'    => $data['name'] ? $data['name'] : 'x247commerce'.date("Y.m.d.h.i.s"),
            'email'     => $data['email'] ? $data['email'] : 'x247commerce'.date("Y.m.d.h.i.s").'@247commerce.co.uk' ,
            'password'  =>'x247commerce',       
            'interface_locale' => 'en_US',
            'is_active' => 1,
            'yext_amlocator_store' => $locationId
        ];

        try {
            if (!$userId) {
                $userModel = $this->userFactory->create();
                $userModel->setData($adminInfo);
                $userModel->setRoleId($this->userHelper->getStaffRole());var_dump('editAdminUser new');
                return $userModel->save();             
            } else {
                $userModel = $this->userFactory->create()->load($userId);var_dump('editAdminUser old');
                $userModel->addData($adminInfo);
                
                return $userModel->save(); 
            }
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
            SourceInterface::SOURCE_CODE => strtolower(str_replace(' ', '_', trim($storeData['name']))),
            SourceInterface::NAME => $storeData['name'],
            SourceInterface::ENABLED => 1,
            SourceInterface::DESCRIPTION => $storeData['description'],
            SourceInterface::LATITUDE => $storeData['lat'],
            SourceInterface::LONGITUDE => $storeData['lng'],
            SourceInterface::COUNTRY_ID => $storeData['country'],
            SourceInterface::POSTCODE => $storeData['zip'],
            'amlocator_store' => $locationId
        ];
        try {
            if (!$sourceCode) {
                $source = $this->sourceInterface;
                $source->setData($sourceData);
                return $source->save();
                
            } else {
                $source = $this->sourceInterface->load($sourceCode);
                $source->addData($sourceData);
                return $source->save();            
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