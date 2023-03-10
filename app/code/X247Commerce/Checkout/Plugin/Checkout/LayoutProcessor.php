<?php

namespace X247Commerce\Checkout\Plugin\Checkout;

class LayoutProcessor
{
    protected $paymentModelConfig;
    protected $locationModel;
    protected $storeLocationContextInterface;
    public function __construct(
        \Magento\Payment\Model\Config $paymentModelConfig,
        \Amasty\Storelocator\Model\Location $locationModel,
        \X247Commerce\Checkout\Api\StoreLocationContextInterface $storeLocationContextInterface
    )
    {
        $this->paymentModelConfig = $paymentModelConfig;
        $this->locationModel = $locationModel;
        $this->storeLocationContextInterface = $storeLocationContextInterface;
    }

    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        $result
    ) {
        $locationId = $this->storeLocationContextInterface->getStoreLocationId();
        $locationPostcode = $locationId ? $this->locationModel->load($locationId)->getZip() : "";
        $result['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shipping-address-fieldset']['children']['postcode']['value'] = $locationPostcode;

        return $result;
    }
}
