<?php

namespace X247Commerce\KlaviyoSMS\Cron;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Psr\Log\LoggerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Framework\App\ResourceConnection;

class SendPickupReminder
{
    protected $scopeConfig;
	protected $resource;
	protected $logger;
    protected $orderRepository;
    protected $orderCollectionFactory;
	
    public function __construct(
    	LoggerInterface $logger,
        OrderRepositoryInterface $orderRepository,
		ScopeConfigInterface $scopeConfig,
		ResourceConnection $resource,
		CollectionFactory $orderCollectionFactory
	)
    {
        $this->scopeConfig = $scopeConfig;
		$this->resource = $resource;
		$this->logger = $logger;
        $this->orderRepository = $orderRepository;
        $this->orderCollectionFactory = $orderCollectionFactory;
    }

    public function execute()
    {
    	$connection = $this->resource->getConnection();

		$storepickupTable = $this->resource->getTableName('amasty_storepickup_order');
		$deliverytableName = $this->resource->getTableName('amasty_amcheckout_delivery');
		$compareDay = date('Y-m-d 00:00:00');

    	$orderCollection = $this->orderCollectionFactory->create();

        $orderCollection->getSelect()
        	->joinLeft(
	            ['aam' => 'amasty_amcheckout_delivery'],
	            'aam.order_id = main_table.entity_id',
	            ['delivery_order_id' => 'order_id', 'delivery_date' => 'date', 'delivery_time' => 'time']
	        )
			->joinLeft(
	            ['aso' => 'amasty_storepickup_order'],
	            'aso.order_id = main_table.entity_id',
	            ['pickup_order_id' => 'order_id', 'pickup_date' => 'date', 'pickup_store_id' => 'store_id', 'time_from']
	        )
	        ->where('aam.date = ? OR aso.date = ?', $compareDay);
	        // ->where('sms_reminder != ?', 1);
        foreach($orderCollection as $order)
        {
			$orderData = $order->getData();
        	if (!empty($orderData['pickup_date']) && strpos($orderData['pickup_date'], '00:00:00') !== false) {
				if ($orderData['time_from']) {
	        		$orderId = $orderData['pickup_order_id']; // Replace with the order ID you want to retrieve
					$store_id = $orderData['pickup_store_id']; 
					$collectionTime = date('H:i:s',$orderData['time_from']);			
					
					$time2 = date('H:i:s');
					
					// Create DateTime objects for the two times
					$dateTime1 = \DateTime::createFromFormat('H:i:s', $collectionTime);
					$dateTime2 = \DateTime::createFromFormat('H:i:s', $time2);
					// Calculate the difference between the two DateTime objects
					$interval = $dateTime1->diff($dateTime2);

					// Get the difference in hours
					$hoursDifference = $interval->h;			
					
					try {
						if($hoursDifference > 0 && $hoursDifference < 2){				
							if($orderData['kl_sms_consent'] == '"1"' && $orderData['sms_reminder'] != '1' && $store_id != ''){
								$selectStore = $connection->select()
									->from('amasty_amlocator_location')
									->where('id = ?', $store_id);
								$resultStore = $connection->fetchAll($selectStore);
								$storeName = isset($resultStore[0]['name']) ? $resultStore[0]['name'] : '';
								$storeAddress = isset($resultStore[0]['address']) ? $resultStore[0]['address'] : '';
								$storePostcode = isset($resultStore[0]['zip']) ? $resultStore[0]['zip'] : '';
							
								$billingAddress = $order->getBillingAddress();				
								$telephone = $billingAddress->getTelephone();			
								
								if (substr($telephone, 0, 1) === "0") {
									$telephone = substr_replace($telephone, "+44", 0, 1);
								}else{
									$telephone ="+44".$telephone;
								}
								
								// Do something with the order data
								// Send Klaviyo event
								echo $klaviyoApiParams = '{
								  "data": {
									"type": "event",
									"attributes": {
									  "profile": {
										"$email": "'.$orderData['customer_email'].'",
										"$phone_number":"'.$telephone.'",
										"$country":"United Kingdom",
										"$pickup_date":"'.date('d-m-Y').'"
									  },
									  "metric": {
										"name": "Delivery date",
										"service": "'.date('d-m-Y').'" 
									  },
									  "properties": {                
										"OrderNumber": "'.$orderData['increment_id'].'",
										"OrderType": "collection",
										"CutsomerName": "'.$billingAddress->getFirstname().' '.$billingAddress->getLastname().'",
										"CollectionDate": "'.date('d-m-Y').'",						
										"CollectionTime": "'.$collectionTime.'",							
										"StoreName": "'.$storeName.'",							
										"StoreAddress": "'.$storeAddress.'",							
										"StorePostcode": "'.$storePostcode.'"						
									  },
									  "value": '.$orderData['grand_total'].',
									  "unique_id": "'.$orderData['increment_id'].'" 
									}
								  }
								}';
								$this->logger->info($klaviyoApiParams);
								$this->sendRequest($klaviyoApiParams);
								$order->setSmsReminder('1');
								$order->save();
							}					
						}				
					} catch (\Exception $e) {
						echo $e->getMessage();
						$this->logger->error('Klaviyo STOREPICKUP SMS reminder Order id'.$orderId.' Error '.$e->getMessage());
						continue;
					}
				}
        	}
        	if (!empty($orderData['delivery_date']) && !empty($orderData['delivery_time']) && $orderData["delivery_date"] && $orderData['delivery_time']) {
				$orderDeliveryId = $orderData['delivery_order_id']; // Replace with the order ID you want to retrieve
				$orderDeliveryTime = $orderData['delivery_time']; 
				$orderDeliveryDate = $orderData['delivery_date'];
				
				$deliveryTime = $orderDeliveryTime.':00:00';
				$deliverytime2 = date('H:i:s');
				
				// Create DateTime objects for the two times
				$deliverydateTime1 = \DateTime::createFromFormat('H:i:s', $deliveryTime);
				$deliverydateTime2 = \DateTime::createFromFormat('H:i:s', $deliverytime2);
				// Calculate the difference between the two DateTime objects
				$deliveryinterval = $deliverydateTime1->diff($deliverydateTime2);

				// Get the difference in hours
				$deliveryhoursDifference = $deliveryinterval->h;

				try {
					if($deliveryhoursDifference > 0 && $deliveryhoursDifference < 2){	
						$orderDeliveryData = $orderData;				
						if($orderDeliveryData['kl_sms_consent'] == '"1"' && $orderDeliveryData['sms_reminder'] != '1'){
						
							$billingDeliveryAddress = $order->getBillingAddress();				
							$delierytelephone = $billingDeliveryAddress->getTelephone();

							$selectStore = $connection->select()
								->from('amasty_amlocator_location')
								->where('id = ?', $orderDeliveryData['store_location_id']);
							$resultStore = $connection->fetchAll($selectStore);	
							$storeName = isset($resultStore[0]['name']) ? $resultStore[0]['name'] : '';
							$storeAddress = isset($resultStore[0]['address']) ? $resultStore[0]['address'] : '';
							$storePostcode = isset($resultStore[0]['zip']) ? $resultStore[0]['zip'] : '';
							
							
							if (substr($delierytelephone, 0, 1) === "0") {
								$delierytelephone = substr_replace($delierytelephone, "+44", 0, 1);
							}else{
								$delierytelephone ="+44".$delierytelephone;
							}
							
							// Do something with the order data
							// Send Klaviyo event
							echo $klaviyoDelieryApiParams = '{
							  "data": {
								"type": "event",
								"attributes": {
								  "profile": {
									"$email": "'.$orderDeliveryData['customer_email'].'",
									"$phone_number":"'.$delierytelephone.'",
									"$country":"United Kingdom",
									"$pickup_date":"'.date('d-m-Y').'"
								  },
								  "metric": {
									"name": "Delivery date",
									"service": "'.date('d-m-Y').'" 
								  },
								  "properties": {                
									"OrderNumber": "'.$orderDeliveryData['increment_id'].'",
									"OrderType": "delivery",
									"CutsomerName": "'.$billingDeliveryAddress->getFirstname().' '.$billingDeliveryAddress->getLastname().'",							
									"DeliveryDate": "'.date('d-m-Y').'",							
									"DeliveryTime": "'.$orderDeliveryTime.':00:00",
									"StoreName": "'.$storeName.'",							
									"StoreAddress": "'.$storeAddress.'",							
									"StorePostcode": "'.$storePostcode.'"						
								  },
								  "value": '.$orderDeliveryData['grand_total'].',
								  "unique_id": "'.$orderDeliveryData['increment_id'].'" 
								}
							  }
							}';	
							$this->logger->info($klaviyoDelieryApiParams);				
							$this->sendRequest($klaviyoDelieryApiParams);
							$order->setSmsReminder('1');
							$order->save();
						}
					}
				} catch (\Exception $e) {
					echo $e->getMessage();
					$this->logger->error('Klaviyo DELIVERY SMS reminder Order id'.$orderDeliveryId.' Error '.$e->getMessage());
					continue;
				}
        	}
        }
    }	

    private function sendRequest($params)
    {
		$klaviyoPrivateApiKey = $this->scopeConfig->getValue('klaviyo_reclaim_general/general/private_api_key');
        $curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => 'https://a.klaviyo.com/api/events/',
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'POST',
		  CURLOPT_POSTFIELDS => $params,
		  CURLOPT_HTTPHEADER => array(
			'revision: 2023-01-24',
			'Content-Type: application/json',
			'Accept: application/json',
			'Authorization: Klaviyo-API-Key '.$klaviyoPrivateApiKey
		  ),
		));
		$response = curl_exec($curl);
		curl_close($curl);
		echo $response;
    }
}





