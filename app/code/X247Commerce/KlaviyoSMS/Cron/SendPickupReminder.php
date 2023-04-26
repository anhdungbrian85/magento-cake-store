<?php

namespace X247Commerce\KlaviyoSMS\Cron;

use Magento\Framework\App\Config\ScopeConfigInterface;
use \Psr\Log\LoggerInterface;
use \Magento\Sales\Api\OrderRepositoryInterface;
use \Magento\Sales\Model\Order;


class SendPickupReminder
{
    protected $scopeConfig;
	protected $resource;
	protected $logger;
    protected $orderRepository;
	
    public function __construct(LoggerInterface $logger,
        OrderRepositoryInterface $orderRepository,
		ScopeConfigInterface $scopeConfig,
		\Magento\Framework\App\ResourceConnection $resource)
    {
        $this->scopeConfig = $scopeConfig;
		$this->resource = $resource;
		$this->logger = $logger;
        $this->orderRepository = $orderRepository;
    }

    public function execute()
    {
		$connection = $this->resource->getConnection();
		$tableName = $this->resource->getTableName('amasty_storepickup_order');
		$value = date('Y-m-d 00:00:00');
		$select = $connection->select()
			->from($tableName)
			->where('date = ?', $value);
		$result = $connection->fetchAll($select);
		foreach ($result as $order) {		
			$orderId = $order['order_id']; // Replace with the order ID you want to retrieve
			$store_id = $order['store_location_id']; 
			$collectionTime = date('H:i:s',$order['time_from']); 
			
			try {				
				$orderDetails = $this->orderRepository->get($orderId);
				$orderData = $orderDetails->getData();				
				if($orderData['kl_sms_consent'] == '"1"' && $orderData['sms_reminder'] != '1'){
					$selectStore = $connection->select()
						->from('amasty_amlocator_location')
						->where('id = ?', $store_id);
					$resultStore = $connection->fetchAll($selectStore);
					$storeName = isset($resultStore[0]['name']) ? $resultStore[0]['name'] : '';
					$storeAddress = isset($resultStore[0]['address']) ? $resultStore[0]['address'] : '';
					$storePostcode = isset($resultStore[0]['zip']) ? $resultStore[0]['zip'] : '';
				
					$billingAddress = $orderDetails->getBillingAddress();				
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
					$orderDetails->setSmsReminder('1');
					$orderDetails->save();
				}
			} catch (\Exception $e) {
				echo $e->getMessage();
				$this->logger->error('Klaviyo STOREPICKUP SMS reminder Order id'.$orderId.' Error '.$e->getMessage());
				continue;
			}
		}

		$deliverytableName = $this->resource->getTableName('amasty_amcheckout_delivery');
		$valueDelivery = date('Y-m-d');
		$selectDelivery = $connection->select()
			->from($deliverytableName)
			->where('date = ?', $valueDelivery);
		$resultDelivery = $connection->fetchAll($selectDelivery);
		foreach ($resultDelivery as $orderDelivery) {		
			$orderDeliveryId = $orderDelivery['order_id']; // Replace with the order ID you want to retrieve
			$orderDeliveryTime = $orderDelivery['time']; 
			$orderDeliveryDate = $orderDelivery['date']; 
			
			try {				
				$orderDetailDelivery = $this->orderRepository->get($orderDeliveryId);
				$orderDeliveryData = $orderDetailDelivery->getData();				
				if($orderDeliveryData['kl_sms_consent'] == '"1"' && $orderDeliveryData['sms_reminder'] != '1'){
				
					$billingDeliveryAddress = $orderDetailDelivery->getBillingAddress();				
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
					$orderDetailDelivery->setSmsReminder('1');
					$orderDetailDelivery->save();
				}
			} catch (\Exception $e) {
				echo $e->getMessage();
				$this->logger->error('Klaviyo DELIVERY SMS reminder Order id'.$orderDeliveryId.' Error '.$e->getMessage());
				continue;
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





