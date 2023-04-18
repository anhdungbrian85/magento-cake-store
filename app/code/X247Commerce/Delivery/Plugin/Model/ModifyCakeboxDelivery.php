<?php

namespace X247Commerce\Delivery\Plugin\Model;
use X247Commerce\Checkout\Api\StoreLocationContextInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Amasty\Storelocator\Model\LocationFactory;
use X247Commerce\Delivery\Helper\DeliveryData;

class ModifyCakeboxDelivery
{
    protected $checkoutSession;
    protected $storeLocationContext;
    protected $locationFactory;
    protected $deliveryData;

    public function __construct
    (
        CheckoutSession $checkoutSession,
        StoreLocationContextInterface $storeLocationContext,
        LocationFactory $locationFactory,
        DeliveryData $deliveryData,
    )
    {
        $this->checkoutSession = $checkoutSession;
        $this->storeLocationContext = $storeLocationContext;
        $this->locationFactory = $locationFactory;
        $this->deliveryData = $deliveryData;
    }

    public function afterCollectRates(\X247Commerce\Delivery\Model\Carrier\CakeboxDelivery $CakeboxDelivery, $result)
    {
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

        $rateShipping = $this->deliveryData->getRateShipping() ?? [];
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
        }
        $allRates = $result->getAllRates();
        foreach ($allRates as &$rate) {
            $rate->setPrice($ratePrice);
        }

        return $result;
    }
}
