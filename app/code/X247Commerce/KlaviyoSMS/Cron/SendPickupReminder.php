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
			try {				
				$order = $this->orderRepository->get($orderId);
				$orderData = $order->getData();				
				if($orderData['kl_sms_consent'] == '"1"'){
				
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
							"$pickup_date":"'.date('Y-m-d').'"
						  },
						  "metric": {
							"name": "Delivery date",
							"service": "'.date('Y-m-d').'" 
						  },
						  "properties": {                
							"OrderNumber": "'.$orderData['increment_id'].'"							
						  },
						  "value": '.$orderData['grand_total'].',
						  "unique_id": "'.$orderData['increment_id'].'" 
						}
					  }
					}';	   
					$this->sendRequest($klaviyoApiParams);
				}
			} catch (\Exception $e) {
				echo $e->getMessage();
				$this->logger->error('Klaviyo SMS reminder Order id'.$orderId.' Error '.$e->getMessage());
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





