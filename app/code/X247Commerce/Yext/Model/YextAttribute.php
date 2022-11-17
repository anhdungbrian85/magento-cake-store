<?php
namespace X247Commerce\Yext\Model;

class YextAttribute
{
    const YEXT_ATTRIBUTE_CODE = 'yext_entity_id';

    protected $locationCollectionFactory;
    protected $locationFactory;
    protected $attributeFactory;
    protected $logger;

    public function __construct(
    \Amasty\Storelocator\Model\ResourceModel\Location\CollectionFactory $locationCollectionFactory,
    \Amasty\Storelocator\Model\LocationFactory $locationFactory,
    \Amasty\Storelocator\Model\AttributeFactory $attributeFactory,
    \Psr\Log\LoggerInterface $logger
    ) {
        $this->locationCollectionFactory = $locationCollectionFactory;
        $this->locationFactory = $locationFactory;
        $this->attributeFactory = $attributeFactory;
        $this->logger = $logger;
    }

    public function getYextEntityAttributeId()
    {
    	return $this->attributeFactory->create()->load(self::YEXT_ATTRIBUTE_CODE, 'attribute_code')->getAttributeId();
    }

    public function getLocationByYext($value)
    {
    	$yextAttributeId = $this->getYextEntityAttributeId();
    	$attributesData = [$yextAttributeId => $value];
    	$location = $this->locationCollectionFactory->create()
                         ->applyAttributeFilters($attributesData)->getFirstItem();

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
        $data['actions_serialized'] = isset($input['primaryProfile']['facebookPageUrl']) ? $input['primaryProfile']['facebookPageUrl'] : '' ;
        
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
            // $insert[self::YEXT_ATTRIBUTE_CODE] = $yexyEntityId;
            // $insert["actions_serialized"] = '{"type":"Magento\\CatalogRule\\Model\\Rule\\Condition\\Combine","attribute":null,"operator":null,"value":true,"is_value_processed":null,"aggregator":"all"}';
            // $insert['schedule'] = '';
            $location = $this->getLocationByYext($yexyEntityId);

            if (empty($location)) {
                // add new location
                // $this->logger->log('600', 'location not exits');die();
                $locationModel = $this->locationFactory->create();
                $locationModel->setData($insert);
                $locationModel->save();
            } else {
                //edit location
                $locationModel = $this->locationFactory->create();
                // $locationModel->load($location->getId());
                $locationModel->setData($insert);
                $locationModel->save();
            }
            return $locationModel->getId();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return false;
        }
    }
}