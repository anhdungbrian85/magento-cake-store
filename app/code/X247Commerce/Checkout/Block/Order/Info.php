<?php

namespace X247Commerce\Checkout\Block\Order;

use Amasty\StorePickupWithLocator\Model\Carrier\Shipping;
// use Magento\Framework\Stdlib\DateTime;

class Info extends \Magento\Sales\Block\Adminhtml\Order\View\Info
{
	protected $locationFactory;

	protected $locationResource;

	protected $orderRepository;

	protected $timezone;
	
	public function __construct(
		\Amasty\Storelocator\Model\LocationFactory $locationFactory,
		\Amasty\Storelocator\Model\ResourceModel\Location $locationResource,
		\Amasty\StorePickupWithLocator\Model\OrderRepository $orderRepository,
		\Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
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
		$this->orderRepository = $orderRepository;
		$this->timezone = $timezone;
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

	public function getOrderPickUp($order)
	{
		if ($order->getShippingMethod() == Shipping::SHIPPING_NAME) {
			$orderPickUp = $this->orderRepository->getByOrderId($order->getId());
			$timeFrom = $orderPickUp->getTimeFrom();
			$timeFrom = $this->timezone->date(new \DateTime("@$timeFrom"))->format('h:i A');

			$timeTo = $orderPickUp->getTimeTo();
			$timeTo = $this->timezone->date(new \DateTime("@$timeTo"))->format('h:i A');

			$datePickUp = date_format(date_create($orderPickUp->getDate()),"Y/m/d");
			return [
				'date' => $datePickUp,
				'timeFrom' => $timeFrom,
				'timeTo' => $timeTo
			];
		}
		return false;
	}
}