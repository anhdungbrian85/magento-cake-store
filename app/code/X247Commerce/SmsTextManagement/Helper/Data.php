<?php

namespace X247Commerce\SmsTextManagement\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_resource;
    protected $_orderCollectionFactory;
    protected $_checkoutSession;
    protected $_customerSession;
    protected $_orderFactory;
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->_resource = $resource;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_checkoutSession = $checkoutSession;
        $this->_customerSession = $customerSession;
        $this->_orderFactory = $orderFactory;
        parent::__construct($context);
    }

    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getApiConfig($config)
    {
        return $this->getConfig("smstextmanagement/sms/" . $config);
    }
    public function getCartConfig($config)
    {
        return $this->getConfig("smstextmanagement/cart/" . $config);
    }
    public function getQuoteData($value)
    {
        return $this->getQuote()->getData($value);
    }
    public function getCustomerSession()
    {
        return $this->_customerSession;
    }
    public function getQuote()
    {
        return $this->_checkoutSession->getQuote();
    }
    public function getIsCustomerLogin()
    {
        return $this->_customerSession->isLoggedIn();
    }
    public function getOrder()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->_orderFactory->create()->load($this->getOrderId());
        return $order;
    }
    public function getOrderId()
    {
        return $this->_checkoutSession->getLastOrderId();
    }
    public function getSendNotifications($order)
    {
        if (!$this->getApiConfig("active")) {
            return;
        }
        $data = $this->getJsonData($order);
$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/InvoicePayObserver.log');
$logger = new \Zend_Log();
$logger->addWriter($writer);
$logger->info('text message');
$logger->info(print_r($data, true));
        $response1 = $this->sendRequest($data[0]);
        $response2 = $this->sendRequest($data[1]);
        $response[0] = $response1;
        $response[1] = $response2;
        return json_encode($response);
    }
    public function sendRequest($data)
    {
        $username = $this->getApiConfig('username');
        $password = $this->getApiConfig('password');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->getApiConfig('api_url'));
        //curl_setopt($ch, CURLOPT_SSLVERSION, 6);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,  json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $data = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $data; //($httpcode >= 200 && $httpcode < 300) ? $data : false;
    }
    public function sendCancelRequest($compainId)
    {
        $username = $this->getApiConfig('username');
        $password = $this->getApiConfig('password');
        $url = $this->getApiConfig('cancel_api_url') . $compainId;
        $ch = curl_init();
        //https://www.tmsmsserver.co.uk/api/smscampaign/CampaignUid
        curl_setopt($ch, CURLOPT_URL, $url);
        //curl_setopt($ch, CURLOPT_SSLVERSION, 6);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $data = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $data; //($httpcode >= 200 && $httpcode < 300) ? $data : false;
    }
    public function getJsonData($orderObj)
    {
        $connection = $this->_resource->getConnection();
        $orderCollection = $this->_orderCollectionFactory->create();

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
                ->where('aam.order_id = ? OR aso.order_id = ?', $orderObj->getId())
                ->limit(1);
        $order = $orderCollection->getFirstItem();
        $orderData = $order->getData();
        $orderDeliveryTime = '';
        $orderDate = '';
        if (!empty($orderData['pickup_date']) && strpos($orderData['pickup_date'], '00:00:00') !== false) {
            if ($orderData['time_from']) {
                $orderId = $orderData['pickup_order_id'];
                $store_id = $orderData['pickup_store_id']; 
                $orderTime = date('H:i:s',$orderData['time_from']);
                $orderDeliveryTime = $orderData['time_from'];
                $orderDate = $orderData['pickup_date'];
                $selectStore = $connection->select()->from('amasty_amlocator_location')->where('id = ?', $store_id);
                $resultStore = $connection->fetchAll($selectStore);
                $storeId = isset($resultStore[0]['id']) ? $resultStore[0]['id'] : '';
                $storeName = isset($resultStore[0]['name']) ? $resultStore[0]['name'] : '';
                $storeAddress = isset($resultStore[0]['address']) ? $resultStore[0]['address'] : '';
                $storePhone = isset($resultStore[0]['phone']) ? $resultStore[0]['phone'] : '';
                $billingAddress = $order->getBillingAddress();              
                $telephone = $billingAddress->getTelephone();           
                
                if (substr($telephone, 0, 1) === "0") {
                    $telephone = substr_replace($telephone, "+44", 0, 1);
                }else{
                    $telephone ="+44".$telephone;
                }
            }
        }
        if (!empty($orderData['delivery_date']) && !empty($orderData['delivery_time']) && $orderData["delivery_date"] && $orderData['delivery_time']) {
            $orderDeliveryId = $orderData['delivery_order_id']; 
            $orderDeliveryTime = $orderData['delivery_time']; 
            $orderDeliveryDate = $orderData['delivery_date'];

            $orderDate = $orderData['delivery_date'];
            $orderTime = date('H:i:s',$orderData['delivery_time']);
            $billingDeliveryAddress = $order->getBillingAddress();              
            $telephone = $billingDeliveryAddress->getTelephone();

            $selectStore = $connection->select()
                ->from('amasty_amlocator_location')
                ->where('id = ?', $orderData['store_location_id']);
            $resultStore = $connection->fetchAll($selectStore); 
            $storeId = isset($resultStore[0]['id']) ? $resultStore[0]['id'] : '';
            $storeName = isset($resultStore[0]['name']) ? $resultStore[0]['name'] : '';
            $storeAddress = isset($resultStore[0]['address']) ? $resultStore[0]['address'] : '';
            $storePhone = isset($resultStore[0]['phone']) ? $resultStore[0]['phone'] : '';
            
            
            if (substr($telephone, 0, 1) === "0") {
                $telephone = substr_replace($delierytelephone, "+44", 0, 1);
            }else{
                $telephone ="+44".$delierytelephone;
            }
        }
        
        $orderType = 'collection';
        if (strpos($order->getIncrementId(), 'DEL') !== false) {
            $orderType = 'delivery';
        }

        date_default_timezone_set('Europe/London');
        $dateParts = explode("-", $orderDate);
        $collection = date('Y-m-d', strtotime("$dateParts[2]-$dateParts[1]-$dateParts[0]"));
        $time = explode(":", $orderDeliveryTime);
        $collectionDate = new \DateTime($collection);

        $current = date('Y-m-d', time());
        $currentDate = new \DateTime($current);
        $diff = date_diff($currentDate, $collectionDate);
        $dif = $diff->format("%R%a");
        $schedule = "";
        if ($dif > 0) {
            $date = $collectionDate;
            $date->setTime(8, 00);
            $schedule = $date->format('Y/m/d\TH:i:s');
        } else {
            $currentDate->setTime((int)date('H', time()), (int)date('i', time()) + 1);
            $schedule = $currentDate->format('Y/m/d\TH:i:s');
        }
        $data[0]['recipients'][0]['mobilenumber'] = $telephone;
        $data[0]['recipients'][0]['p1'] = $order->getBillingAddress()->getFirstname() . " " . $order->getBillingAddress()->getLastname();
        $data[0]['recipients'][0]['p2'] = $order->getIncrementId();
        $data[0]['recipients'][0]['p3'] = $orderType;
        $data[0]['recipients'][0]['p4'] = $orderDate;
        $data[0]['recipients'][0]['p5'] = $orderTime;
        $data[0]['recipients'][0]['p6'] = $storeName;
        $data[0]['recipients'][0]['p7'] = $storePhone;
        // if ($order->getStorepickupMethod() != 1) {
        //     $data[0]['messagetext'] = $this->getApiConfig('message');
        // } else {
        //     $data[0]['messagetext'] = $this->getApiConfig('delivery_message');
        // }

        $data[0]['from'] = $this->getApiConfig('from');

        $data[0]['scheduleddate'] = $schedule; //2015-02-12T14:00:00.5190555+00:00

        $data[0]['route'] = 1;
        $data[0]['usertag'] = $storeId;
        $data[0]['usercampaignid'] = $order->getIncrementId();
        /////////////////////
        $date = $collectionDate; //new \DateTime($startdate);
        $date->add(new \DateInterval('P1D'));

        $date->setTime(19, 00); //$date->setTime((int)$time[0],(int)$time[1]);
        $schedule = $date->format('Y/m/d\TH:i:s');
        $data[1]['recipients'][0]['mobilenumber'] = $telephone;
        $data[1]['recipients'][0]['p1'] = $order->getBillingAddress()->getFirstname() . " " . $order->getBillingAddress()->getLastname();
        $data[1]['recipients'][0]['p2'] = $order->getIncrementId();
        $data[1]['recipients'][0]['p3'] = $orderType;
        $data[1]['recipients'][0]['p4'] = $orderDate;
        $data[1]['recipients'][0]['p5'] = $orderTime;
        $data[1]['recipients'][0]['p6'] = $storeName;
        $data[1]['recipients'][0]['p7'] = $storePhone;
        $data[1]['messagetext'] = $this->getApiConfig('courtesy');
        $data[1]['from'] = $this->getApiConfig('from');
        $data[1]['scheduleddate'] = $schedule; //2015-02-12T14:00:00.5190555+00:00
        $data[1]['route'] = 1;
        $data[1]['usertag'] = $order->getStoreList();
        $data[1]['usercampaignid'] = $order->getIncrementId();
        return $data;
    }
    public function getCurrentDateTime()
    {
        date_default_timezone_set('Europe/London');
        $startTime = date("Y-m-d\TH:i:s");
        //$convertedTime = date('Y-m-d H:i:s', strtotime('+15 minutes', strtotime($startTime)));
        return $startTime;
        //return $this->date->gmtDate();
    }
    public function getCheckoutExpDateTime($startTime = '')
    {
        date_default_timezone_set('Europe/London');
        if ($startTime == '') {
            $startTime = $this->getCurrentDateTime();
        }
        return date('Y-m-d\TH:i:s', strtotime('+15 minutes', strtotime($startTime)));
    }
}