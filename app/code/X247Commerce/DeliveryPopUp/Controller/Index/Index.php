<?php
namespace X247Commerce\DeliveryPopUp\Controller\Index;
use X247Commerce\Checkout\Api\StoreLocationContextInterface;

class Index extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;

	protected $storeLocationContextInterface;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		StoreLocationContextInterface $storeLocationContextInterface,
		\Magento\Framework\View\Result\PageFactory $pageFactory)
	{
		$this->_pageFactory = $pageFactory;
		$this->storeLocationContextInterface = $storeLocationContextInterface;
		return parent::__construct($context);
	}

	public function execute()
	{
		$data = $this->getRequest()->getPostValue();
		if ($data['address']) {
			$this->storeLocationContextInterface->setStoreLocationId(1);
		}

		return $this->resultRedirectFactory->create()->setPath('');
		
	}
}