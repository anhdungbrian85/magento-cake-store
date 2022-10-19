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

class Location extends \Amasty\Storelocator\Block\Location
{	

	protected $formKey;

	protected $locationModel;

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
        \Amasty\Storelocator\Api\ReviewRepositoryInterface  $reviewRepository,
        array $data = []
	) {
        $this->locationModel = $locationModel;
		$this->formKey = $formKey;
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
}