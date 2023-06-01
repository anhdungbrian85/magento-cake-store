<?php

namespace X247Commerce\Customer\Block\Account\EventList;

class Index extends \Magento\Framework\View\Element\Template
{
	public $eventFactory;
	public $customerSession;
	public $date;
	public $escaper;

	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Magento\Framework\Escaper $escaper,
		\X247Commerce\Customer\Model\EventFactory $eventFactory,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Framework\Stdlib\DateTime\DateTime $date,
		array $data = [],
	) {
		parent::__construct($context, $data);
		$this->escaper = $escaper;
		$this->eventFactory = $eventFactory;
		$this->customerSession = $customerSession;
		$this->date = $date;
	}

	public function getEventListCustomer()
	{
		return $this->eventFactory->create()->getCollection()->addFieldToFilter('customer_id', ['eq' => $this->getCustomerId()]);
	}

	public function separateDate($date)
	{
		$date = explode(" ", $date)[0];
		$data = explode("-", $date);

		return $data;
	}

	public function getCustomerId()
	{
		return $this->customerSession->getCustomer()->getId();
	}

	public function getYearPresent()
	{
		$date = $this->date->gmtDate();
		$year = explode("-", $date);

		return (int) $year[0];
	}

	public function getListYear()
	{
		$listYear = [];

		for ($i = $this->getYearPresent(); $i >= $this->getYearPresent() - 50; $i--) {
			$listYear[] = $i;
		}

		return $listYear;
	}

	public function getMonthLabel($value)
	{
		$arrayMonth = [
			'01' => 'Jan',
			'02' => 'Feb',
			'03' => 'Mar',
			'04' => 'Apr',
			'05' => 'May',
			'06' => 'Jun',
			'07' => 'Jul',
			'08' => 'Aug',
			'09' => 'Sep',
			'10' => 'Oct',
			'11' => 'Nov',
			'12' => 'Dec',
		];

		return $arrayMonth[$value];
	}
}