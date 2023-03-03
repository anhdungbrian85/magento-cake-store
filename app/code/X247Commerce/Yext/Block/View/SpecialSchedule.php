<?php

namespace X247Commerce\Yext\Block\View;

use Amasty\Storelocator\Model\AttributeFactory;
use X247Commerce\Yext\Helper\YextHelper;
use Magento\Framework\App\ResourceConnection;
use X247Commerce\Yext\Model\YextAttribute;
use X247Commerce\Yext\Model\ResourceModel\HolidayHours\CollectionFactory;
use Amasty\Base\Model\Serializer;
use Amasty\Storelocator\Model\ConfigProvider;
use Amasty\Storelocator\Model\ImageProcessor;
use Amasty\Storelocator\Model\Location as locationModel;
use Amasty\Storelocator\Model\ResourceModel\Gallery\Collection;
use Amasty\Storelocator\Model\Review as reviewModel;
use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;

class SpecialSchedule extends \Amasty\Storelocator\Block\View\Location
{
    protected $attributeFactory;
    protected $yextHelper;
    protected $resource;
    protected $connection;
    protected $yextAttribute;
    protected $holidayHoursCollection;

    public function __construct(
        AttributeFactory $attributeFactory,
        YextHelper $yextHelper, 
        ResourceConnection $resource,
        YextAttribute $yextAttribute,
        CollectionFactory $holidayHoursCollection,
        Template\Context $context,
        Registry $coreRegistry,
        ConfigProvider $configProvider,
        locationModel $locationModel,
        Collection $galleryCollection,
        ImageProcessor $imageProcessor,
        Serializer $serializer,
        CountryFactory $countryFactory,
        RegionFactory $regionFactory,
        \Amasty\Storelocator\Helper\Data $dataHelper,
        array $data = []
    ) 
    {
        parent::__construct($context, $coreRegistry, $configProvider, $locationModel, $galleryCollection, $imageProcessor, $serializer, $countryFactory, $regionFactory, $dataHelper);
        $this->attributeFactory = $attributeFactory;
        $this->yextHelper = $yextHelper;
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->yextAttribute = $yextAttribute;
        $this->holidayHoursCollection = $holidayHoursCollection;
    }

    /**
     * Get Reopen Date value by Location 
     *
     * @param \Amasty\Storelocator\Model\Location
     * 
     * @return string||null
     */
    public function getReopenDateByLocation($location)
    {
        if ($this->getLocation()) {
            $location = $this->getLocation();
        }
        $reopenString = $this->yextAttribute->getAttributeValueByLocation($location, 'temporarily_closed');
        if ($reopenString) {
            $reopenArray = explode(" ", $reopenString);
            if (isset($reopenArray[2])) {
                return date_format(date_create($reopenArray[2]),"d/m/Y");
            } else {
                return $reopenString;
            }
        }
    }
    /**
     * Get Holiday Hours value by Location 
     *
     * @param \Amasty\Storelocator\Model\Location
     * 
     * @return string||null
     */
    public function getClosestHolidatHoursByLocation($location)
    {
        if ($this->getLocation()) {
            $location = $this->getLocation();
        }
        $holidayHours = $this->holidayHoursCollection->create()->addFieldToFilter('store_id', ['eq' => $location->getId()]);
        if ($holidayHours->addFieldToFilter('date', ['gteq' => date('Y-m-d')])->setOrder('date','ASC')->getFirstItem()->getDate()) {
            return $holidayHours->addFieldToFilter('date', ['gteq' => date('Y-m-d')])->setOrder('date','ASC')->getFirstItem();
        } else {
            return $holidayHours->addFieldToFilter('date', ['lteq' => date('Y-m-d')])->setOrder('date','DESC')->getFirstItem();
        }
    }

}
