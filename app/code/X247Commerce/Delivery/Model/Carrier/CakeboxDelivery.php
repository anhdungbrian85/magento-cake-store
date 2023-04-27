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
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/collect_rates.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('Start Debug');

        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $customerPostcode = $this->checkoutSession->getCustomerPostcode() ?? $this->storeLocationContext->getCustomerPostcode();
        $storeId = $this->checkoutSession->getStoreLocationId() ?? $this->storeLocationContext->getStoreLocationId();
        $storePostcode = $this->locationFactory->create()->load($storeId)->getZip();
        $logger->info('Customer Postcode: ' . $customerPostcode);
        $logger->info('Store Id: ' . $storeId);
        $logger->info('Store Postcode: ' . $storePostcode);
        $responseApi = json_decode($this->deliveryData->calculateDistance($customerPostcode, $storePostcode), true);
        $customerLocationData = $this->deliveryData->getLongAndLatFromPostCode($customerPostcode);
        $storeLocationData = $this->deliveryData->getLongAndLatFromPostCode($storePostcode);
        $logger->info('Customer Location Data::' . print_r($customerLocationData, true));
        $logger->info('Store Location Data::' . print_r($storeLocationData, true));
        if ($customerLocationData['status'] && $storeLocationData['status']) {
            $distance = $this->deliveryPopUpHelperData->calculateDistanceWithOutUnit(
                $customerLocationData['data']['lat'],
                $customerLocationData['data']['lng'],
                $storeLocationData['data']['lat'],
                $storeLocationData['data']['lng'],
                'miles'
            );
        } else {
            if (isset($responseApi["rows"][0]["elements"][0]["distance"]["text"])) {
                $distance = (float) strtok($responseApi["rows"][0]["elements"][0]["distance"]["text"], ' ');
            } else {
                $distance = 1;
            }
        }

        $logger->info('Distance: ' . $distance);
        $shippingPrice = $this->getConfigData('price');

        $rateShipping = $this->deliveryData->getRateShipping() ? json_decode($this->deliveryData->getRateShipping(), true) : [];

        $ratePrice = -1;
        foreach ($rateShipping as $item => $value) {
            if (!empty($value)) {
                if ($distance > $value["from_distance"] && $distance <= $value["to_distance"]) {
                    $ratePrice = (float) $value["price"];
                }
            }
        }
        if ($ratePrice < 0) {
            return false;
        } else {
            $shippingPrice = $ratePrice;
        }

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
