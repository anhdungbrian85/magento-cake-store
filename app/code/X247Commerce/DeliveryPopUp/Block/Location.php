<?php

namespace X247Commerce\DeliveryPopUp\Block;

use Magento\Framework\Data\Form\FormKey;
use Amasty\Base\Model\Serializer;
use Amasty\Storelocator\Api\Validator\LocationProductValidatorInterface;
use Amasty\Storelocator\Block\View\Reviews;
use Amasty\Storelocator\Helper\Data;
use Amasty\Storelocator\Model\BaseImageLocation;
use Amasty\Storelocator\Model\ConfigProvider;
use Amasty\Storelocator\Model\ImageProcessor;
use Amasty\Storelocator\Model\Location as LocationModel;
use Amasty\Storelocator\Model\ResourceModel\Attribute\Collection as AttributeCollection;
use Amasty\Storelocator\Model\ResourceModel\Location\Collection as LocationCollection;
use Amasty\Storelocator\Model\ResourceModel\Location\CollectionFactory;
use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Widget\Block\BlockInterface;
use Amasty\Storelocator\Model\LocationFactory;

class Location extends \Amasty\Storelocator\Block\Location
{	

    private $locationCollection;

	protected $formKey;

    private $locationCollectionFactory;

    protected $locationFactory;

	protected $locationModel;

    private $reviewRepository;

    protected $dataConfig;
    
    protected $imageProcessor;

	public function __construct(
        LocationModel $locationModel,
		FormKey $formKey,
		Context $context,
        Registry $coreRegistry,
        EncoderInterface $jsonEncoder,
        File $ioFile,
        Data $dataHelper,
        AttributeCollection $attributeCollection,
        Serializer $serializer,
        ConfigProvider $configProvider,
        ImageProcessor $imageProcessor,
        Product $productModel,
        CollectionFactory $locationCollectionFactory,
        BaseImageLocation $baseImageLocation,
        LocationProductValidatorInterface $locationProductValidator,
        LocationFactory $locationFactory,
        \Amasty\Storelocator\Api\ReviewRepositoryInterface  $reviewRepository,
        \X247Commerce\DeliveryPopUp\Helper\Data $dataConfig,
        array $data = []
	) {
        $this->locationModel = $locationModel;
		$this->formKey = $formKey;
        $this->locationFactory = $locationFactory;
        $this->locationCollectionFactory = $locationCollectionFactory;
        $this->reviewRepository = $reviewRepository;
        $this->dataConfig = $dataConfig;
        $this->imageProcessor = $imageProcessor;
		parent::__construct(
			$context, 
			$coreRegistry,
			$jsonEncoder, 
			$ioFile, 
			$dataHelper, 
			$attributeCollection,
			$serializer, 
			$configProvider,
			$imageProcessor, 
			$productModel, 
			$locationCollectionFactory, 
			$baseImageLocation,
			$locationProductValidator, 
			$reviewRepository, 
			$data
		);
	}

	public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    public function getSaveAction()
    {
        return $this->getUrl('deliverypopup/index/selectlocation');
    }

    public function getLeftBlockHtml()
    {
        $html = $this->setTemplate('X247Commerce_DeliveryPopUp::popup/list-location.phtml')->toHtml();
        return $html;
    }

    public function getOpenTime() {
        return $this->locationModel->getWorkingTimeToday();
    }

    public function getWorkingTime() {
       
        return $this->locationModel->getWorkingTime("monday");
    }

    public function getJsonLocations()
    {
        $locationArray = [];
        $locationArray['items'] = [];
        /** @var LocationModel $location */
        foreach ($this->getLocationCollection() as $location) {
            $data = $location->getData();
            if ($markerImg = $location->getMarkerImg()) {
                $location['marker_url'] = $this->imageProcessor->getImageUrl(
                    [ImageProcessor::AMLOCATOR_MEDIA_PATH, $location->getId(), $markerImg]
                );
            }
            $locationArray['items'][] = $location->getFrontendData();
        }
        $locationArray['totalRecords'] = count($locationArray['items']);
        $store = $this->_storeManager->getStore(true)->getId();
        $locationArray['currentStoreId'] = $store;
        //remove double spaces
        $locationArray['block'] = $this->dataHelper->compressHtml($this->getLeftBlockHtml());

        return $this->jsonEncoder->encode($locationArray);
    }

    public function getLocationCollection()
    {
        $needToPrepareCollection = false;
        $pageNumber = (int)$this->getRequest()->getParam('p') ? (int)$this->getRequest()->getParam('p') : 1;
        
        if (!$this->locationCollection) {
            $this->locationCollection = $this->locationCollectionFactory->create();
            $this->locationCollection->applyDefaultFilters();
            $this->locationCollection->joinScheduleTable();
            $this->locationCollection->joinMainImage();
            $needToPrepareCollection = true;
        }
        if ($attributesData = $this->prepareWidgetAttributes()) {
            $this->locationCollection->clear();
            $this->locationCollection->applyAttributeFilters($attributesData);
            $needToPrepareCollection = true;
        }

        if ($needToPrepareCollection) {

            $this->locationCollection->setCurPage($pageNumber);
            $this->locationCollection->setPageSize($this->configProvider->getPaginationLimit());
            if ($this->getRequest()->getPost('delivery-type') == 0 || $this->getRequest()->getPost('delivery-type') == 2) {
                $this->locationCollection->addFieldToFilter('curbside_enabled',1);
            }
            if ($this->getRequest()->getPost('delivery-type') == 2) {
                $this->locationCollection->getSelect()->where('main_table.id NOT IN (?)', ['asda_tbl' => new \Zend_Db_Expr("SELECT asda_location_id FROM ".$this->locationCollection->getTable('store_location_asda_link'))]);
            }
            
            $this->reviewRepository->loadReviewForLocations($this->locationCollection->getAllIds());

            foreach ($this->locationCollection as $location) {
                /** @var LocationModel $location */
                $location->setRating($this->getRatingHtml($location));
                $location->setTemplatesHtml();
            }
        }

        return $this->locationCollection;
    }
}
