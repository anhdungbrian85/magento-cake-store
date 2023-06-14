<?php

namespace X247Commerce\ChangeOrderStatus\Cron;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Transaction;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Sales\Model\Convert\Order;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Model\Service\InvoiceService;
use X247Commerce\ChangeOrderStatus\Helper\Data;

class ChangeOrderStatus
{
	protected CollectionFactory $orderCollectionFactory;
	protected InvoiceService $invoiceService;
	protected Order $convertOrder;
	protected Data $changeOrderStatusHelper;
	protected Transaction $transaction;
    protected ResourceConnection $resourceConnection;
    protected SessionManagerInterface $coreSession;

	public function __construct(
		CollectionFactory $orderCollectionFactory,
		InvoiceService $invoiceService,
		Order $convertOrder,
		Data $changeOrderStatusHelper,
		Transaction $transaction,
        ResourceConnection $resourceConnection,
        SessionManagerInterface $coreSession

    ) {
		$this->orderCollectionFactory = $orderCollectionFactory;
		$this->invoiceService = $invoiceService;
		$this->convertOrder = $convertOrder;
		$this->changeOrderStatusHelper = $changeOrderStatusHelper;
		$this->transaction = $transaction;
        $this->resourceConnection = $resourceConnection;
        $this->coreSession = $coreSession;
	}


	public function execute()
	{

		$statuses = array( 'pending', 'processing' );
        $dayToChangeOrder = (int) $this->changeOrderStatusHelper->getNumberDayChangeStatus();
        $thisdate = date_create(date('Y-m-d'));
        $compareDay = date_sub($thisdate, date_interval_create_from_date_string("$dayToChangeOrder days"));
        $compareDay = date_format($compareDay, 'Y-m-d');;

		$collection = $this->orderCollectionFactory->create()
			->addFieldToSelect('*')
			->addFieldToFilter('status', ['in' => $statuses] )
            ->addFieldToFilter('auto_complete_flag', ['neq' => 1]);

        $collection->getSelect()->joinLeft(
            ['aam' => 'amasty_amcheckout_delivery'],
            'aam.order_id = main_table.entity_id',
            ['delivery_date' => 'date']
        );

        $collection->getSelect()->joinLeft(
            ['aso' => 'amasty_storepickup_order'],
            'aso.order_id = main_table.entity_id',
            ['pickup_date' => 'date']
        );
        $collection->getSelect()->where('aam.date <= ? OR aso.date <= ?', $compareDay);
        $limit = $this->coreSession->getLimitCompleteOrder();
        if (empty($limit)) {
            $limit = 50;
        }
        $this->coreSession->unsLimitCompleteOrder();
        $collection->getSelect()->limit($limit);

		foreach ($collection as $order) {
            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/autocomplete-order.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);

			$date = (strpos($order->getIncrementId(), 'DEL') !== false) ? $order->getData('delivery_date') : '';
            if (empty($date)) {
                $date = $order->getData('pickup_date');
            }
			if (!empty($date)) {
                $today = date('Y-m-d');
                $today = strtotime($today);
                $converted = strtotime($date);
                try {
                    if ( ( $today - $converted ) > 0 && ($today- $converted)/86400 >= $dayToChangeOrder ) {
                        if($order->canInvoice()) {
                            $invoice = $this->invoiceService->prepareInvoice($order);
                            $invoice->register();
                            $invoice->save();
                            $transactionSave = $this->transaction->addObject(
                                $invoice
                            )->addObject(
                                $invoice->getOrder()
                            );
                            $transactionSave->save();
                        }

                        if ($order->canShip()) {
                            $orderShipment = $this->convertOrder->toShipment($order);
                            foreach ($order->getAllItems() as $item) {
                                if (!$item->getQtyToShip() || $item->getIsVirtual()) {
                                    continue;
                                }
                                $qty = $item->getQtyToShip();
                                $shipmentItem = $this->convertOrder->itemToShipmentItem($item)->setQty($qty);
                                $orderShipment->addItem($shipmentItem);
                            }
                            $orderShipment->register();
                            $orderShipment->getOrder()->setIsInProcess(true);
                            $orderShipment->getOrder()->save();
                            $logger->info('Created shipment id: '.$orderShipment->getId());

                        }
                    }
                    $logger->info('Complete order with entity_id: '.$order->getId());
                }   catch (\Exception $e) {
                    $logger->info('Cannot complete order with entity_id: '.$order->getId(). ', increment_id: '.$order->getIncrementId(). ' - '.$e->getMessage());
                }
			} else {
                $logger->info('Cannot complete order with entity_id: '.$order->getId(). ', increment_id: '.$order->getIncrementId(). ' because this order do not have delivery_date or pickup_date');
            }
            $this->saveAutoCompleteFlag($order);
		}
	}

    /**
     * @return void
     */
    private function saveAutoCompleteFlag($order): void
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('sales_order');
        $data = ["auto_complete_flag" => 1];
        $where = ['entity_id = ?' => $order->getId()];
        $connection->update($tableName, $data, $where);
    }
}
