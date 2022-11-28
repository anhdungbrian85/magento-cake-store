<?php 

namespace X247Commerce\Sales\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class SendEmailForAdmin
{
    protected $request;

    protected $scopeConfig;

    protected $_checkoutSession;

    protected $locationFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Amasty\Storelocator\Model\LocationFactory $locationFactory,
        ScopeConfigInterface $scopeConfig
    ){
        $this->scopeConfig = $scopeConfig;
        $this->locationFactory = $locationFactory;
        $this->request = $request;
        $this->_checkoutSession = $checkoutSession;
    }
    public function afterGetEmailCopyTo(\Magento\Sales\Model\Order\Email\Container\IdentityInterface $subject, $result)
    {

        $session = $this->_checkoutSession;
        $dataOrder =  $session->getLastRealOrder()->getData();
        $storeIdPickUp = $dataOrder["store_location_id"];

        if (!empty($storeIdPickUp)) {
            $storeCollection = $this->locationFactory->create()->getCollection()->addFieldToFilter('id', ['eq' => $storeIdPickUp]);
            $dataLocation = $storeCollection->getData();
            $emailAdmin = $dataLocation[0]["email"];
            array_push($result, $emailAdmin);
        }
        return $result; 
    }
}
