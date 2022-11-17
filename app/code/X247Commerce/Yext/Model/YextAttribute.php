<?php
namespace X247Commerce\Yext\Model;

class YextAttribute
{
    protected const AMASTY_AMLOCATOR_STORE_ATTRIBUTE = 'amasty_amlocator_store_attribute';

    protected $locationCollectionFactory;

    protected $locationFactory;

    protected $attributeFactory;

    protected $logger;

    protected $resource;

    protected $connection;

    public function __construct(
    \Amasty\Storelocator\Model\ResourceModel\Location\CollectionFactory $locationCollectionFactory,
    \Amasty\Storelocator\Model\LocationFactory $locationFactory,
    \Amasty\Storelocator\Model\AttributeFactory $attributeFactory,
    \Psr\Log\LoggerInterface $logger,
    \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->locationCollectionFactory = $locationCollectionFactory;
        $this->locationFactory = $locationFactory;
        $this->attributeFactory = $attributeFactory;
        $this->logger = $logger;
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
    }

    public function getYextEntityAttributeId()
    {
        return $this->attributeFactory->create()->load('yext_entity_id', 'attribute_code')->getAttributeId();
    }

    public function getLocationByYext($value)
    {
        $yextAttributeId = $this->getYextEntityAttributeId();
        $attributesData = [$yextAttributeId => [$value]];

        $locations = $this->locationCollectionFactory->create();
        $location = $locations->applyAttributeFilters($attributesData)->getFirstItem();

        return $location;
    }

    public function responseDataProcess($input)
    {
        $data = [];
        $data['name'] = isset($input['primaryProfile']['name']) ? $input['primaryProfile']['name'] : '';
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

    public function deleteLocation($yexyEntityId)
    {
        try {
            $location = $this->getLocationByYext($yexyEntityId);
            if ($location) {
                $location->delete();
            }            
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
    public function editLocation($data, $yexyEntityId)
    {
        try {
            $insert = $this->responseDataProcess($data);
            // $this->logger->log('600', print_r($insert, true));
            $location = $this->getLocationByYext("'$yexyEntityId'");
            if (!$location->getId()) {
                // add new location
                $locationModel = $this->locationFactory->create();
                $locationModel->setData($insert); 
                $locationModel->save();
                if ($locationModel) {
                   $this->insertYextEntityIdValue([$locationModel->getId() => $yexyEntityId]);
                }
                return $locationModel;
            } else {
                //edit location
                $location->addData($insert);
                $location->save();
                if ($location->getId()) {
                   $this->insertYextEntityIdValue([$location->getId() => $yexyEntityId]);
                }
                return $location;
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    public function insertYextEntityIdValue($insert)
    {
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