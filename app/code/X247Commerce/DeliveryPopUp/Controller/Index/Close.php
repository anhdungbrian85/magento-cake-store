<?php
namespace X247Commerce\DeliveryPopUp\Controller\Index;

use X247Commerce\Checkout\Api\StoreLocationContextInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Http\Context as HttpContext;
use X247Commerce\DeliveryPopUp\Helper\Data as PopUpHelper;

class Close extends \Magento\Framework\App\Action\Action
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
        $this->storeLocationContextInterface->setPopupClosed(true); 
        
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData(['success' => true]);
    }

}