<?php
namespace X247Commerce\Sales\Observer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\ResourceConnection;
class ChangeIncrementIdPrefix implements ObserverInterface
{
    protected $registry;
    protected $invoiceCollectionFactory;
    protected $logger;
    protected $locatorSourceResolver;
    protected $storeLocationContextInterface;
    protected $yextAttribute;
    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoiceCollectionFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Registry $registry,
        \X247Commerce\StoreLocatorSource\Model\ResourceModel\LocatorSourceResolver $locatorSourceResolver,
        \X247Commerce\Checkout\Api\StoreLocationContextInterface $storeLocationContextInterface,
        \X247Commerce\Yext\Model\YextAttribute $yextAttribute,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->invoiceCollectionFactory = $invoiceCollectionFactory;
        $this->logger = $logger;
        $this->registry = $registry;
        $this->locatorSourceResolver = $locatorSourceResolver;
        $this->yextAttribute = $yextAttribute;
        $this->storeLocationContextInterface = $storeLocationContextInterface;
        $this->customerSession = $customerSession;
    }
    public function execute(Observer $observer)
    {
        $quote = $observer->getQuote();        
        $order = $observer->getOrder();        
        $incrementId = $quote->getReservedOrderId();
        $prefix = $this->getPrefix($order);
        $order->setIncrementId($prefix.$incrementId);
        
    }

    public function getPrefix($order = null)
    {
        if ($order) {
            $locationId = empty($order->getStoreLocationId()) ? $order->getStoreLocationId() : $this->customerSession->getStoreLocationId();
        } else {
            $locationId = $this->customerSession->getStoreLocationId();
        }
        
        $yextEntityIdOfLocation = $this->yextAttribute->getYextEntityIdByLocationId($locationId);        
        $yextPrefix = $yextEntityIdOfLocation ? substr($yextEntityIdOfLocation, -3, 3).'-' : '';
        $deliveryType = $this->customerSession->getDeliveryType();
        switch ($deliveryType) {
            case 0:
            case 2:
                $deliPrefix = 'COL';
                break;
            case 1:
                $deliPrefix = 'DEL';
                break;
            
            default:
                $deliPrefix = 'COL';
                break;
        }
        if (strpos($yextEntityIdOfLocation, 'CBK') !== false) {
            $deliPrefix = 'KIO';
        }
        $prefix = $yextPrefix.$deliPrefix.'-';
        // var_dump($locationId);var_dump($deliveryType);var_dump($yextPrefix);die();
        return $prefix;
    }
}