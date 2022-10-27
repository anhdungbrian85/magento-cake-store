<?php

namespace X247Commerce\Checkout\Block\Order;


class Info extends \Magento\Sales\Block\Adminhtml\Order\View\Info
{
	protected $locationFactory;

	protected $locationResource;
	
	public function __construct(
		\Amasty\Storelocator\Model\LocationFactory $locationFactory,
		\Amasty\Storelocator\Model\ResourceModel\Location $locationResource,
		\Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Helper\Admin $adminHelper,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Customer\Api\CustomerMetadataInterface $metadata,
        \Magento\Customer\Model\Metadata\ElementFactory $elementFactory,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        array $data = []
	) {
		$this->locationFactory = $locationFactory;
		$this->locationResource = $locationResource;
		parent::__construct(
			$context, 
			$registry, 
			$adminHelper,
			 $groupRepository, 
			 $metadata, 
			 $elementFactory, 
			 $addressRenderer, 
			 $data
			);
	}

	public function getStoreName($id) {
		if(empty($id)){

			$data["name"]= Null;
		} else {
			$storeCollection = $this->locationFactory->create();
			$store = $this->locationResource->load($storeCollection,$id);
			$data = $storeCollection->getData();
		}
		return $data["name"];
	}
}