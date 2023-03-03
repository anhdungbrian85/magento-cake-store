<?php 

namespace X247Commerce\Sales\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class BeforeSendEmailshipment
{

    protected $scopeConfig;

    protected $_coreSession;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Session\SessionManagerInterface $coreSession
    ){
        $this->scopeConfig = $scopeConfig;
        $this->_coreSession = $coreSession;
    }
    public function beforeSend(\Magento\Sales\Model\Order\Email\Sender\ShipmentSender $subject, $shipment)
    {
        $order = $shipment->getOrder();
        $this->_coreSession->start();
        $this->_coreSession->setX247Order($order);
        return [$shipment];
    }

    public function afterSend(\Magento\Sales\Model\Order\Email\Sender\ShipmentSender $subject, $result)
    {
        
        $this->_coreSession->start();
        $this->_coreSession->unsX247Order();
        return $result;
    }
}
