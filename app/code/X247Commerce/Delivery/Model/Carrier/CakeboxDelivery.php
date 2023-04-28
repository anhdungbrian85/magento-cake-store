<?php
declare(strict_types=1);

namespace X247Commerce\Delivery\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;
use X247Commerce\Checkout\Api\StoreLocationContextInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Amasty\Storelocator\Model\LocationFactory;
use X247Commerce\Delivery\Helper\DeliveryData;
use X247Commerce\DeliveryPopUp\Helper\Data as DeliveryPopUpHelperData;

class CakeboxDelivery extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{

    protected $_code = 'cakeboxdelivery';

    protected $_isFixed = true;

    protected $_rateResultFactory;

    protected $_rateMethodFactory;

    protected $checkoutSession;

    protected $storeLocationContext;

    protected $locationFactory;
    protected $deliveryData;

    protected $deliveryPopUpHelperData;

    public function __construct(
        DeliveryPopUpHelperData $deliveryPopUpHelperData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        CheckoutSession $checkoutSession,
        StoreLocationContextInterface $storeLocationContext,
        LocationFactory $locationFactory,
        DeliveryData $deliveryData,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->checkoutSession = $checkoutSession;
        $this->storeLocationContext = $storeLocationContext;
        $this->locationFactory = $locationFactory;
        $this->deliveryData = $deliveryData;
        $this->deliveryPopUpHelperData = $deliveryPopUpHelperData;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }   

        $rateShipping = $this->deliveryData->getRateShipping() ? json_decode($this->deliveryData->getRateShipping(), true) : [];

        $defaultShippingPrice = $this->getConfigData('price');
        if (empty($rateShipping)) {
            return $this->setShippingRate($defaultShippingPrice, $request);
        }

        $customerPostcode = ($request->getDestPostcode() && $request->getDestPostcode() != '-') ? $request->getDestPostcode() : $this->checkoutSession->getCustomerPostcode();
        $storeLocationId = $this->checkoutSession->getStoreLocationId() ?: $this->storeLocationContext->getStoreLocationId();
        $storeLocation = $this->locationFactory->create()->load($storeLocationId);
        $storePostcode = $storeLocation->getZip();

        if ($storePostcode == $customerPostcode) {
            $minimumShippingPrice = (float)array_values($rateShipping)[0]['price'];
            return $this->setShippingRate($minimumShippingPrice, $request);
        }

        $storeLatLng = $this->getStoreLatLng($storeLocation);
        $customerLocationData = $this->deliveryData->getLongAndLatFromPostCode($customerPostcode);

        $distance = $this->getDistance($customerLocationData, $storeLatLng, $customerPostcode, $storePostcode);
        $maximumShippingPrice = (float)array_values($rateShipping)[(count($rateShipping)-1)]['price'];

        if (empty($distance)) {
            return $this->setShippingRate($maximumShippingPrice, $request);
        }
        
        $rateShipping = $this->deliveryData->getRateShipping() ? json_decode($this->deliveryData->getRateShipping(), true) : [];

        $ratePrice = $maximumShippingPrice;
        foreach ($rateShipping as $value) {
            if ($distance > $value["from_distance"] && $distance <= $value["to_distance"]) {
                $ratePrice = (float) $value["price"];
            }
        }

        return $this->setShippingRate($ratePrice, $request);
    }

    /**
     * get Distance by lat, lng or postcode
     **/
    protected function getDistance($customerLocationData, $storeLatLng, $customerPostcode, $storePostcode) {
        $distance = false;
        if ($customerLocationData['status'] && $storeLatLng) {
            // get Distance by Lat, Lng
            $distance = $this->deliveryPopUpHelperData->calculateDistanceWithOutUnit(
                $customerLocationData['data']['lat'],
                $customerLocationData['data']['lng'],
                $storeLatLng['lat'],
                $storeLatLng['lng'],
                'miles'
            );
        } else {
            // if there is not lat, long data, then get Distance by postcode
            $responseApi = json_decode($this->deliveryData->calculateDistance($customerPostcode, $storePostcode), true);
            
            if (isset($responseApi["rows"][0]["elements"][0]["distance"]["text"])) {
                $distance = (float) strtok($responseApi["rows"][0]["elements"][0]["distance"]["text"], ' ');
            }
        }
        return $distance;
    }

    /**
     * get store location lat, lng
     **/
    protected function getStoreLatLng($storeLocation)
    {
        if ($storeLocation->getData('lat') && $storeLocation->getData('lng')) {
            return [
                'lat' => $storeLocation->getData('lat'),
                'lng' => $storeLocation->getData('lng')
            ];
        }   else {
            $storeLocationData = $this->deliveryData->getLongAndLatFromPostCode($storePostcode);
            if ($storeLocationData['status']) {
                return [
                    'lat' => $storeLocationData['data']['lat'],
                    'lng' => $storeLocationData['data']['lng']
                ];
            }
        }
        return false;
    }


    protected function setShippingRate($shippingPrice, $request)
    {
        $result = $this->_rateResultFactory->create();
        if ($shippingPrice != false) {
            $method = $this->_rateMethodFactory->create();

            $method->setCarrier($this->_code);
            $method->setCarrierTitle($this->getConfigData('title'));

            $method->setMethod($this->_code);
            $method->setMethodTitle($this->getConfigData('name'));

            if ($request->getFreeShipping() === true) {
                $shippingPrice = '0.00';
            }

            $method->setPrice($shippingPrice);
            $method->setCost($shippingPrice);

            $result->append($method);
        }
        return $result;
    }
    /**
     * getAllowedMethods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('name')];
    }
}
