<?php
declare(strict_types=1);

namespace X247Commerce\Delivery\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;
use X247Commerce\Checkout\Api\StoreLocationContextInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Amasty\Storelocator\Model\LocationFactory;
use X247Commerce\Delivery\Helper\DeliveryData;

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

    public function __construct(
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
        $deliveryType = $this->checkoutSession->getDeliveryType() ?? $this->storeLocationContext->getDeliveryType();
        if ($deliveryType != 1) {
            return false;
        }

        $customerPostcode = $this->checkoutSession->getCustomerPostcode() ?? $this->storeLocationContext->getCustomerPostcode();
        $storeId = $this->checkoutSession->getStoreLocationId() ?? $this->storeLocationContext->getStoreLocationId();
        $storePostcode = $this->locationFactory->create()->load($storeId)->getZip();

        $responseApi = json_decode($this->deliveryData->calculateDistance($customerPostcode, $storePostcode), true);
        if (isset($responseApi["rows"][0]["elements"][0]["distance"]["text"])) {           
            $distance = (float) strtok($responseApi["rows"][0]["elements"][0]["distance"]["text"], ' ');
        } else {
            return false;
        }

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

        if ($shippingPrice !== false) {
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
