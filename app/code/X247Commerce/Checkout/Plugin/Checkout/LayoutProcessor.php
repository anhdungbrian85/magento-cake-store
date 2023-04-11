<?php

namespace X247Commerce\Checkout\Plugin\Checkout;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class LayoutProcessor
{
    protected $paymentModelConfig;
    protected $locationModel;
    protected $storeLocationContextInterface;
    protected $scopeConfig;

    public function __construct(
        \Magento\Payment\Model\Config $paymentModelConfig,
        \Amasty\Storelocator\Model\Location $locationModel,
        \X247Commerce\Checkout\Api\StoreLocationContextInterface $storeLocationContextInterface,
        ScopeConfigInterface $scopeConfig

    )
    {
        $this->paymentModelConfig = $paymentModelConfig;
        $this->locationModel = $locationModel;
        $this->storeLocationContextInterface = $storeLocationContextInterface;
        $this->scopeConfig = $scopeConfig;
    }

    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        $result
    ) {
        $locationId = $this->storeLocationContextInterface->getStoreLocationId();
        $locationPostcode = $locationId ? $this->locationModel->load($locationId)->getZip() : "";
        $result['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shipping-address-fieldset']['children']['postcode']['value'] = $locationPostcode;

        if (!$this->scopeConfig->getValue('x247commerce_checkout/billing/enable', ScopeInterface::SCOPE_STORE)) {
            $result['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children']['afterMethods']['children']['billing-address-form']['children']['form-fields']['children']
            ['telephone']['validation'] = ['required-entry' => false, 'max_text_length' => 255, 'min_text_length' => 1];
        }

        return $result;
    }
}
