<?php

namespace X247Commerce\Checkout\Block;

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
use Amasty\Storelocator\Api\ReviewRepositoryInterface;
use X247Commerce\DeliveryPopUp\Helper\Data as PopUpHelper;
use X247Commerce\Checkout\Api\StoreLocationContextInterface;
use Magento\Framework\App\Http\Context as HttpContext;

class StoreLocatorConfig extends \Amasty\Storelocator\Block\Location
{
 	protected FormKey       $formKey;

    protected PopUpHelper   $popupHelper;

    protected StoreLocationContextInterface $storeLocationContextInterface;

    protected HttpContext $httpContext;

    protected \Magento\Framework\App\Request\Http $request;

 	public function __construct(
        \Magento\Framework\App\Request\Http $request,
        FormKey $formKey,
        PopUpHelper $popupHelper,
        StoreLocationContextInterface $storeLocationContextInterface,
        HttpContext $httpContext,
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
        ReviewRepositoryInterface  $reviewRepository,
        array $data = []
        
 	) {
 		$this->formKey = $formKey;
        $this->popupHelper = $popupHelper;
        $this->storeLocationContextInterface = $storeLocationContextInterface;
        $this->httpContext = $httpContext;
        $this->request = $request;
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

 	public function postCode()
    {
        return $this->getUrl('x247commerce_deliverypopup/index/index');
    }

    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    public function getSearchRadius()
    {
        return $this->popupHelper->getPopupSearchRadius();
    }

    public function getSerializedAmLocatorConfig()
    {
        $amLocatorConfig = [
            'amMediaUrl' => $this->escapeHtml($this->getAmStoreMediaUrl()),
            'mapZoom' => $this->escapeHtml($this->getMapZoom()),
            'automaticLocate' => (bool)$this->getAutomaticLocate(),
            'enableClustering' => 0,
            'enableSuggestionClickSearch' => (bool)$this->getSuggestionClickSearch(),
            'enableCountingDistance' => (bool)$this->getCountingDistance(),
            'distanceConfig' => $this->escapeHtml($this->getDistanceConfig()),
            'useGeoConfig' => (bool)$this->getGeoUse(),
            'allowedCountries' => $this->getAllowedCountries(),
            'ajaxCallUrl' => $this->escapeHtml($this->getUrl('deliverypopup/index/ajax') . $this->getQueryString()),
            'ajaxSelectUrl' => $this->escapeHtml($this->getUrl('deliverypopup/index/selectLocation')),
            'useBrowserLocation' => (bool)$this->getUseBrowserLocation(),
            'jsonLocations' => $this->getJsonLocations(),
            'imageLocations' => $this->escapeHtml($this->getViewFileUrl('Amasty_Storelocator::images/')),
            'mapId' => $this->escapeHtml($this->getMapId()),
            'mapContainerId' => $this->escapeHtml($this->getMapContainerId()),
            'showSearch' => 1
        ];
        return $this->serializer->serialize($amLocatorConfig);
    }
}
