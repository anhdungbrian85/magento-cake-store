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

    public function __construct(
    \Amasty\Storelocator\Model\ResourceModel\Location\CollectionFactory $locationCollectionFactory,
    \Amasty\Storelocator\Model\LocationFactory $locationFactory,
    \Amasty\Storelocator\Model\AttributeFactory $attributeFactory,
    \Psr\Log\LoggerInterface $logger,
    \X247Commerce\Yext\Helper\YextHelper $yextHelper, 
    \Magento\Framework\App\ResourceConnection $resource,
    \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
    \Magento\Framework\Filesystem\Io\File $file,
    \Amasty\Storelocator\Model\ImageProcessor $imageProcessor
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
    }

    public function getYextEntityAttributeId()
    {
        //get id of attribute yext_entity_id in table amasty_amlocator_attribute
        return $this->attributeFactory->create()->load('yext_entity_id', 'attribute_code')->getAttributeId();
    }

    public function getLocationByYext($value)
    {
        //get amasty_amlocator_location by attribute yext_entity_id
        $yextAttributeId = $this->getYextEntityAttributeId();
        $attributesData = [$yextAttributeId => [$value]];

        $locations = $this->locationCollectionFactory->create();
        $location = $locations->applyAttributeFilters($attributesData)->getFirstItem();

        return $location;
    }

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

    public function editLocation($data, $yextEntityId)
    {
        try {
            
            $insert = $this->responseDataProcess($data['primaryProfile']);
            
            $this->logger->log('600', print_r($data, true));die();
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
        // var_dump($newFileName); var_dump($tmpDir);die();
        if ($result) {
            return $name;
        } else {
            return false;
        }
        
    }
    protected function getAmastyMediaDir()
    {
        return $this->directoryList->getPath($this->directoryList::MEDIA) . DIRECTORY_SEPARATOR . 'amasty/amlocator/gallery';
    }
}