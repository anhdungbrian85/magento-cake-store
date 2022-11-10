<?php
namespace X247Commerce\Yext\Model;

class YextAttribute
{
    protected $locationCollectionFactory;

    protected $attributeFactory;

    public function __construct(
    \Amasty\Storelocator\Model\ResourceModel\Location\CollectionFactory $locationCollectionFactory,
    \Amasty\Storelocator\Model\AttributeFactory $attributeFactory
    ) {
        $this->locationCollectionFactory = $locationCollectionFactory;
        $this->attributeFactory = $attributeFactory;
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
    	$location = $locations->applyAttributeFilters($attributesData);

        return $location;
    }
}