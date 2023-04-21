<?php
namespace X247Commerce\Sales\Observer\Backend;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Registry;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Order\InvoiceFactory;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Model\Order\CreditmemoRepository;
use Magento\Backend\Model\Auth\Session as AdminSession;
use X247Commerce\StoreLocatorSource\Helper\User as UserHelper; 
use Magento\Framework\UrlInterface;
use Magento\Framework\Message\ManagerInterface;


class SalesManagementValidation implements ObserverInterface
{

    protected OrderFactory $orderFactory;
    protected InvoiceFactory $invoiceFactory;
    protected ShipmentRepositoryInterface $shipmentRepository;
    protected CreditmemoRepository $creditMemoRepository;
    protected UserHelper $userHelper;
    protected UrlInterface $url;
    protected ManagerInterface $messageManager;
    

    public function __construct(
        OrderFactory $orderFactory,
        InvoiceFactory $invoiceFactory,
        ShipmentRepositoryInterface $shipmentRepository,
        CreditmemoRepository $creditMemoRepository,
        UserHelper $userHelper,
        UrlInterface $url,
        ManagerInterface $messageManager
    )
    {
        $this->orderFactory = $orderFactory;
        $this->invoiceFactory = $invoiceFactory;
        $this->shipmentRepository = $shipmentRepository;
        $this->creditMemoRepository = $creditMemoRepository;
        $this->userHelper = $userHelper;
        $this->url = $url;
        $this->messageManager = $messageManager;
    }

    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(Observer $observer)
    {
        $action = $observer->getData('controller_action');
        $order = $this->getOrder($observer);
        if (!$order) {
            return $this;
        }

        if (!$this->userHelper->userCanManageOrder($order)) {
            $this->messageManager->addError(__('You don\'t have permission to access this resource'));
            $grid = $this->url->getUrl('*/*/');
            return $action->getResponse()->setRedirect($grid);
        }

        return $this;
    }

    /**
     * retrive order 
     * @param \Magento\Framework\Event\Observer $observer
     * @return mixed
     */
    private function getOrder($observer)
    {
        $request = $observer->getRequest();
        $fullActionName = $request->getFullActionName();
        $order = null;

        switch ($fullActionName) {
            case 'sales_order_view':
                $objectId = $request->getParam('order_id');
                $order = $this->orderFactory->create()->load($objectId); 
            break;
            case 'sales_order_invoice_view':
                $objectId = $request->getParam('invoice_id');
                $object = $this->invoiceFactory->create()->load($objectId);
                $order = $object->getOrder();
            break;
            case 'sales_shipment_view':
                $objectId = $request->getParam('shipment_id');
                $object = $this->shipmentRepository->get($objectId);
                $order = $object->getOrder();
            break;
            case 'sales_creditmemo_view':
                $objectId = $request->getParam('creditmemo_id');
                $object = $this->creditMemoRepository->get($objectId);
                $order = $object->getOrder();
            break;
        }
    
        return $order;
    }


}
