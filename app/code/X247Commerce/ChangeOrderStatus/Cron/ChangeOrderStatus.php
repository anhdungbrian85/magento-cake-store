<?php

namespace X247Commerce\ChangeOrderStatus\Cron;

class ChangeOrderStatus
{

	protected $orderCollectionFactory;
	protected $amsOrderRepository;
	protected $invoiceService;
	protected $convertOrder;
	protected $changeOrderStatusHelper;
	protected $transaction;

	public function __construct(
		\Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
		\Amasty\StorePickupWithLocator\Model\OrderRepository $amsOrderRepository,
		\Magento\Sales\Model\Service\InvoiceService $invoiceService,
		\Magento\Sales\Model\Convert\Order $convertOrder,
		\X247Commerce\ChangeOrderStatus\Helper\Data $changeOrderStatusHelper,
		\Magento\Framework\DB\Transaction $transaction
	) {
		$this->orderCollectionFactory = $orderCollectionFactory;
		$this->amsOrderRepository = $amsOrderRepository;
		$this->invoiceService = $invoiceService;
		$this->convertOrder = $convertOrder;
		$this->changeOrderStatusHelper = $changeOrderStatusHelper;
		$this->transaction = $transaction;
	}


	public function execute()
	{
		$statuses = array( 'pending', 'processing' );
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
        $collection->getSelect()->limit(50);

		$dayToChangeOrder = (int) $this->changeOrderStatusHelper->getNumberDayChangeStatus();

		foreach ($collection as $order) {

            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/autocomplete-order.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);

			$date = $order->getData('delivery_date');
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

                            $order->setStatus('complete');
                            $order->setState('complete');
                        }

                        $order->setData('auto_complete_flag', 1);
                        $order->save();
                    }
                }   catch (\Exception $e) {
                    $logger->info('cannot complete order: '.$order->getId(). ' - '.$e->getMessage());
                    $order->setData('auto_complete_flag', 1);
                    $order->save();
                }
			}
		}
	}
}
