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

    public function __construct(
    \Amasty\Storelocator\Model\ResourceModel\Location\CollectionFactory $locationCollectionFactory,
    \Amasty\Storelocator\Model\LocationFactory $locationFactory,
    \Amasty\Storelocator\Model\AttributeFactory $attributeFactory,
    \Psr\Log\LoggerInterface $logger,
    \X247Commerce\Yext\Helper\YextHelper $yextHelper, 
    \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->locationCollectionFactory = $locationCollectionFactory;
        $this->locationFactory = $locationFactory;
        $this->attributeFactory = $attributeFactory;
        $this->logger = $logger;
        $this->yextHelper = $yextHelper;
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
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
        //process data from json $input to insert into amasty_amlocator_location
        $data = [];
        $data['name'] = isset($input['primaryProfile']['name']) ? $input['primaryProfile']['name'] : '';
        $url_key = '';
        if (isset($input['primaryProfile']['name'])) {
            $url_key = $this->yextHelper->getUrlKeyFromName($input['primaryProfile']['name']);
        }
        $data['url_key'] = $url_key;
        $data['country'] = isset($input['primaryProfile']['address']['countryCode']) ? $input['primaryProfile']['address']['countryCode'] : '' ;
        $data['status'] = 1;
        $data['stores'] = 0;
        $data['city'] = isset($input['primaryProfile']['address']['city']) ? $input['primaryProfile']['address']['city'] : '';
        $data['zip'] = isset($input['primaryProfile']['address']['postalCode']) ? $input['primaryProfile']['address']['postalCode'] : '';
        $data['address'] = isset($input['primaryProfile']['address']['line1']) ? $input['primaryProfile']['address']['line1'] : '' ;
        $data['state'] = isset($input['primaryProfile']['address']['region']) ? $input['primaryProfile']['address']['region'] : '' ;
        $data['lat'] = isset($input['primaryProfile']['cityCoordinate']['latitude']) ? $input['primaryProfile']['cityCoordinate']['latitude'] : '' ;
        $data['lng'] = isset($input['primaryProfile']['cityCoordinate']['longitude']) ? $input['primaryProfile']['cityCoordinate']['longitude'] : '' ;
        $data['description'] = isset($input['primaryProfile']['description']) ? $input['primaryProfile']['description'] : '' ;
        $data['phone'] = isset($input['primaryProfile']['mainPhone']) ? $input['primaryProfile']['mainPhone'] : '' ;
        $data['email'] = isset($input['primaryProfile']['emails'][0]) ? $input['primaryProfile']['emails'][0] : '' ;
        $data['website'] = isset($input['primaryProfile']['facebookPageUrl']) ? $input['primaryProfile']['facebookPageUrl'] : '' ;
        // $data['actions_serialized'] = isset($input['primaryProfile']) ? $input['primaryProfile'] : '' ;

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
            $insert = $this->responseDataProcess($data);
            // $this->logger->log('600', print_r($insert, true));
            $location = $this->getLocationByYext("'$yextEntityId'");
            if (!$location->getId()) {$this->logger->log('600', 'add new location');
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
            $insert = $this->responseDataProcess($data);
            // $this->logger->log('600', print_r($insert, true));
            $location = $this->getLocationByYext("'$yextEntityId'");
            if (!$location->getId()) {$this->logger->log('600', 'edit location');
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
}