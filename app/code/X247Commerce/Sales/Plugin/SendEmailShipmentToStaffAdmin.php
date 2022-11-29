<?php 

namespace X247Commerce\Sales\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class SendEmailShipmentToStaffAdmin
{
    protected $request;

    protected $scopeConfig;

    protected $locationFactory;

    protected $order;

     protected $_coreSession;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \Amasty\Storelocator\Model\LocationFactory $locationFactory,
        \Magento\Sales\Api\Data\OrderInterface $order,
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Session\SessionManagerInterface $coreSession
    ){
        $this->scopeConfig = $scopeConfig;
        $this->locationFactory = $locationFactory;
        $this->request = $request;
        $this->order = $order;
        $this->_coreSession = $coreSession;
    }
    public function afterGetEmailCopyTo(\Magento\Sales\Model\Order\Email\Container\ShipmentIdentity $subject, $result)
    {
        $this->_coreSession->start();
        $orderId = $this->_coreSession->getMessage();
        $order = $this->order->load($orderId);
        $storeLocationId = $order["store_location_id"];

        if (!empty($storeLocationId)) {
            $storeCollection = $this->locationFactory->create()->load($storeLocationId);
            $dataLocation = $storeCollection->getData();
            $emailAdmin = $dataLocation["email"];
            array_push($result, $emailAdmin);
        }
        return $result;
    }
}
