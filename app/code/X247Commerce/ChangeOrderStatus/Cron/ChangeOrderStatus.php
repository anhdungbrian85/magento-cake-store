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
			->addFieldToFilter('status', ['in' => $statuses] );
		$dayToChangeOrder = (int) $this->changeOrderStatusHelper->getNumberDayChangeStatus();
		
		foreach ($collection as $order) {
			$orderData = $this->amsOrderRepository->getByOrderId($order->getId());
			$createdAt = $order->getCreatedAt();
			$date = $orderData->getDate();
			
			if ( $date ) {
				$today = date('Y-m-d');
				$today = strtotime($today);
				$converted = strtotime($date);
				
				if ( ( $today - $converted ) > 0 && ($today- $converted)/86400 >= $dayToChangeOrder ) {
					$order->setStatus(\Magento\Sales\Model\Order::STATE_COMPLETE);
					$order->save();

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
						try {
							//$orderShipment->save();
							$orderShipment->getOrder()->save();

						} catch (\Exception $e) {
							throw new \Magento\Framework\Exception\LocalizedException(
							__($e->getMessage())
							);
						}
					}

				}
			}
		}

	}
}
