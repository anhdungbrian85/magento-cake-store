<?php
declare(strict_types=1);

namespace X247Commerce\Delivery\Model\Carrier;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory;
use Psr\Log\LoggerInterface;
use X247Commerce\Checkout\Api\StoreLocationContextInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Amasty\Storelocator\Model\LocationFactory;
use X247Commerce\Delivery\Helper\DeliveryData;
use X247Commerce\DeliveryPopUp\Helper\Data as DeliveryPopUpHelperData;
use X247Commerce\StoreLocatorSource\Model\ResourceModel\LocatorSourceResolver;

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

    protected $locatorSourceResolver;

    public function __construct(
        LocatorSourceResolver $locatorSourceResolver,
        DeliveryPopUpHelperData $deliveryPopUpHelperData,
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        CheckoutSession $checkoutSession,
        StoreLocationContextInterface $storeLocationContext,
        LocationFactory $locationFactory,
        DeliveryData $deliveryData,
        array $data = []
    ) {
        $this->locatorSourceResolver = $locatorSourceResolver;
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
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/checkout_test.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);

        try {
            if (!$this->getConfigFlag('active')) {
                return false;
            }
            $rateShipping = $this->deliveryData->getRateShipping() ? json_decode($this->deliveryData->getRateShipping(), true) : [];
            $maximumShippingPrice = (float)array_values($rateShipping)[(count($rateShipping)-1)]['price'];
            $defaultShippingPrice = $this->getConfigData('price');
            $minimumShippingPrice = (float)array_values($rateShipping)[0]['price'];

            $customerPostcode = ($request->getDestPostcode() && $request->getDestPostcode() != '-') ? $request->getDestPostcode() : $this->checkoutSession->getCustomerPostcode();

            if (!$customerPostcode) {
                // when empty delivery postcode
                return $this->setShippingRate($maximumShippingPrice, $request);
            }

            $quote = $this->checkoutSession->getQuote();
            $logger->info('$quoteId' . $quote->getId());
            $productSkus = [];
            if (!empty($quote->getAllVisibleItems())) {
                foreach ($quote->getAllVisibleItems() as $quoteItem) {
                    $productSkus[] = $quoteItem->getSku();
                }
            }
            $locationDataFromPostCode = $this->deliveryData->getLongAndLatFromPostCode($customerPostcode);

            $logger->info('$customerPostcode' . $customerPostcode);
            $logger->info('$locationDataFromPostCode'.print_r($locationDataFromPostCode, true));
            if ($locationDataFromPostCode['status']) {

                $location = $this->locatorSourceResolver->getClosestStoreLocationWithPostCodeAndSkus(
                    $customerPostcode,
                    $locationDataFromPostCode['data']['lat'],
                    $locationDataFromPostCode['data']['lng'],
                    $productSkus
                );
                $logger->info('$locationId' . $location->getId());
                if ($location->getId()) {
                    if ($quote->getShippingAddress()->getShippingMethod() == 'cakeboxdelivery_cakeboxdelivery') {
                        $quote->setData('store_location_id', $location->getId());
                        $quote->setData('delivery_type', 1);
                        $quote->save();
                    }
                }
            }

            if (empty($rateShipping)) {
                return $this->setShippingRate($defaultShippingPrice, $request);
            }

            $storePostcode = $location->getZip();

            if (empty($storePostcode)) {
                return $this->setShippingRate($maximumShippingPrice, $request);
            }

            if (strtolower($storePostcode) == strtolower($customerPostcode)) {
                return $this->setShippingRate($minimumShippingPrice, $request);
            }

            $storeLatLng = $this->getStoreLatLng($location);
            $customerLocationData = $this->deliveryData->getLongAndLatFromPostCode($customerPostcode);

            $distance = $this->getDistance($customerLocationData, $storeLatLng, $customerPostcode, $storePostcode);

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
        } catch (\Exception $e) {
            $logger->info('Start collectRates:: ' . $e->getMessage());
            return false;
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
