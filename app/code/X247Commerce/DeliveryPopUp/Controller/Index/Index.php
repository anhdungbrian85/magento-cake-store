<?php
namespace X247Commerce\DeliveryPopUp\Controller\Index;

use X247Commerce\Checkout\Api\StoreLocationContextInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Http\Context as HttpContext;
use X247Commerce\DeliveryPopUp\Helper\Data as PopUpHelper;

class Index extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;
	protected JsonFactory $resultJsonFactory;
	protected $storeLocationContextInterface;
	protected HttpContext $httpContext;
	protected PopUpHelper   $popupHelper;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		StoreLocationContextInterface $storeLocationContextInterface,
		\Magento\Framework\View\Result\PageFactory $pageFactory,
		JsonFactory $resultJsonFactory,
		HttpContext $httpContext,
		PopUpHelper   $popupHelper
	)
	{
		$this->_pageFactory = $pageFactory;
		$this->storeLocationContextInterface = $storeLocationContextInterface;
		$this->resultJsonFactory = $resultJsonFactory;
		$this->httpContext = $httpContext;
		$this->popupHelper = $popupHelper;
		return parent::__construct($context);
	}

	public function execute()
	{
		$resultJson = $this->resultJsonFactory->create();
		$resultJson->setData(
			[
				'showPopup' => $this->shouldShowPopup(),
				'enableAddToCart' => $this->shouldEnableAddToCart()
			]
		);
		
		return $resultJson;	
	}

	private function shouldShowPopup()
    {        
        return !$this->httpContext->getValue(StoreLocationContextInterface::STORE_LOCATION_ID) 
        	&& !$this->httpContext->getValue(StoreLocationContextInterface::POPUP_CLOSED)
        	&& $this->popupHelper->isEnabledPopup();
    }

    private function shouldEnableAddToCart()
    {        
        return !empty($this->httpContext->getValue(StoreLocationContextInterface::STORE_LOCATION_ID)) || !$this->popupHelper->isEnabledPopup();
    }
}