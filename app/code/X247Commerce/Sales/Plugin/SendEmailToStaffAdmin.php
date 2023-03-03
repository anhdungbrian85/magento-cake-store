<?php 

namespace X247Commerce\Sales\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class SendEmailToStaffAdmin
{
    protected $request;

    protected $scopeConfig;

    protected $locationFactory;

    protected $_coreSession;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \Amasty\Storelocator\Model\LocationFactory $locationFactory,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        ScopeConfigInterface $scopeConfig
    ){
        $this->scopeConfig = $scopeConfig;
        $this->locationFactory = $locationFactory;
        $this->request = $request;
        $this->_coreSession = $coreSession;
    }
    public function afterGetEmailCopyTo(\Magento\Sales\Model\Order\Email\Container\OrderIdentity $subject, $result)
    {

        $this->_coreSession->start();
        $orderData = $this->_coreSession->getX247Order()->getData();
        $storeLocationId = $orderData["store_location_id"];
        if (!empty($storeLocationId)) {
            $storeCollection = $this->locationFactory->create()->load($storeLocationId);
            $dataLocation = $storeCollection->getData();
            $emailAdmin = $dataLocation["email"];
            array_push($result, $emailAdmin);
        }
        return $result; 
    }
}
