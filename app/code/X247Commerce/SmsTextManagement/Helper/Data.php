<?php

namespace X247Commerce\SmsTextManagement\Helper;

use Amasty\StorePickupWithLocator\Model\Carrier\Shipping as AmStorePickupShipping;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    CONST ALLOWED_SMS_VARIABLES = ['customer_name', 'order_id', 'order_date', 'order_time', 'store_phone', 'store_name', 'order_type'];

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

    /**
     * @param $config
     * @return mixed
     */
    public function getApiConfig($config)
    {
        return $this->getConfig("smstextmanagement/sms/" . $config);
    }

    /**
     * @param $order
     * @return false|string
     * @throws \Zend_Log_Exception
     */
    public function getSendNotifications($order)
    {
        try {
            $data = $this->getJsonData($order);
            $response = $this->sendRequest($data);
            return json_encode($response);
        } catch (\Exception $exception) {
            // Do nothing, just do not prevent placing order
        }

        return false;
    }

    /**
     * @param $uid
     * @return bool|string
     */
    public function getTmsCampaignData($uid)
    {
        $username = $this->getApiConfig('username');
        $password = $this->getApiConfig('password');
        $ch = curl_init();
        $url = $this->getApiConfig('api_url').'/'.$uid;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
    public function sendRequest($data)
    {
        $username = $this->getApiConfig('username');
        $password = $this->getApiConfig('password');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->getApiConfig('api_url'));
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,  json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    /**
     * @param string $haystack
     * @param array $needles
     * @return array
     */
    public function strposa(string $haystack, array $needles)
    {
        $r = [];
        foreach($needles as $n) {
            if (strpos($haystack, $n) !== false) {
                $r[strpos($haystack, $n)] = $n;
            }
        }
        ksort($r);
        $e = array_values($r);
        $f = [];
        foreach ($e as $k => $v) {
            $f[$k+1] = $v;
        }
        return $f;
    }

    /**
     * @param $orderObj
     * @return array
     * @throws \Exception
     *
     */
    public function getJsonData($orderObj)
    {
        date_default_timezone_set('Europe/London');
        $data = $variableData = [];
        $orderCollection = $this->_orderCollectionFactory->create();
        $orderCollection->getSelect()
                ->joinLeft(
                    ['aam' => 'amasty_amcheckout_delivery'],
                    'aam.order_id = main_table.entity_id',
                    ['delivery_date' => 'date', 'delivery_time' => 'time']
                )
                ->joinLeft(
                    ['aso' => 'amasty_storepickup_order'],
                    'aso.order_id = main_table.entity_id',
                    ['pickup_date' => 'date', 'pickup_store_id' => 'store_id', 'time_from']
                )
                ->joinLeft(
                    ['ams' => 'amasty_amlocator_location'],
                    'main_table.store_location_id = ams.id',
                    ['store_name' => 'ams.name', 'store_phone' => 'ams.phone']
                )
                ->where('aam.order_id = ? OR aso.order_id = ?', $orderObj->getId())
                ->limit(1);

        $order = $orderCollection->getFirstItem();
        $orderData = $order->getData();
        $orderType = $order->getShippingMethod() == AmStorePickupShipping::SHIPPING_NAME ? 'collection' : 'delivery';
        $isCollection = $orderType == 'collection';
        $customerAddress = $isCollection ? $order->getBillingAddress() : $order->getShippingAddress();
        $telephone =  "+44". ltrim($customerAddress->getTelephone(),0);

        $customerName = $customerAddress->getFirstname() . " " . $customerAddress->getLastname();
        $orderDate = $isCollection ? $orderData['pickup_date'] : $orderData['delivery_date'];
        $orderDate = (new \DateTime($orderDate))->format("Y-m-d");
        $orderTime = $isCollection ? date('H:i:s', $orderData['time_from']) : $orderData['delivery_time'];
        $storeName = $orderData['store_name'];
        $storePhone = $orderData['store_phone'];
        $storeId = $orderData['store_location_id'];
        $current = date('Y-m-d', time());
        $currentDate = new \DateTime($current);
        $collectionDate = new \DateTime($orderDate);

        $diff = date_diff($currentDate, $collectionDate);
        $dif = $diff->format("%R%a");

        if ($dif > 0) {
            $date = $collectionDate;
            $date->setTime(8, 00);
            $schedule = $date->format('Y/m/d\TH:i:s');
        } else {
            $currentDate->setTime((int)date('H', time()), (int)date('i', time()) + 1);
            $schedule = $currentDate->format('Y/m/d\TH:i:s');
        }

        $variableData['customer_name'] = $customerName;
        $variableData['order_id'] = $order->getIncrementId();
        $variableData['order_type'] = $orderType;
        $variableData['order_date'] = $orderDate;
        $variableData['order_time'] = $orderTime;
        $variableData['store_name'] = $storeName;
        $variableData['store_phone'] = $storePhone;

        // Confirmation Message
        $smsMessage = $isCollection ? $this->getApiConfig('collection_message') : $this->getApiConfig('delivery_message');

        $variablesPos = $this->strposa($smsMessage, self::ALLOWED_SMS_VARIABLES);
        $tmsMessage = $this->convertMessage($smsMessage);

        if (!empty($variablesPos)) {
            foreach ($variablesPos as $pos => $variableKey) {
                $data['recipients'][0]['p'.$pos] = $variableData[$variableKey];
            }
        }

        $data['recipients'][0]['mobilenumber'] = $telephone;
        $data['recipients'][0]['messageparts'] = 1;
        $data['messagetext'] = $tmsMessage;
        $data['from'] = $this->getApiConfig('from');
        $data['scheduleddate'] = $schedule;
        $data['route'] = 1;
        $data['usertag'] = $storeId;
        $data['usercampaignid'] = $order->getIncrementId();
        $data['messageparts'] = 1;

        return $data;
    }

    protected function convertMessage($origMessage)
    {
        $variablesPos = $this->strposa($origMessage, self::ALLOWED_SMS_VARIABLES);
        $tmsMessage = $origMessage;
        foreach ($variablesPos as $pos => $variable) {
            $tmsMessage = str_replace('['.$variable.']', '[p'.$pos.']', $tmsMessage);
        }
        return $tmsMessage;
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
