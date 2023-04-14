<?php

namespace X247Commerce\Checkout\Plugin\Checkout;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Klaviyo\Reclaim\Helper\ScopeSetting;

class LayoutProcessor
{
    protected $paymentModelConfig;
    protected $locationModel;
    protected $storeLocationContextInterface;
    protected $scopeConfig;
    protected $_klaviyoScopeSetting;

    public function __construct(
        \Magento\Payment\Model\Config $paymentModelConfig,
        \Amasty\Storelocator\Model\Location $locationModel,
        \X247Commerce\Checkout\Api\StoreLocationContextInterface $storeLocationContextInterface,
        ScopeConfigInterface $scopeConfig,
        ScopeSetting $klaviyoScopeSetting

    )
    {
        $this->paymentModelConfig = $paymentModelConfig;
        $this->locationModel = $locationModel;
        $this->storeLocationContextInterface = $storeLocationContextInterface;
        $this->scopeConfig = $scopeConfig;
        $this->_klaviyoScopeSetting = $klaviyoScopeSetting;
    }

    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        $result
    ) {
        $locationId = $this->storeLocationContextInterface->getStoreLocationId();
        $locationPostcode = $locationId ? $this->locationModel->load($locationId)->getZip() : "";
        $result['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shipping-address-fieldset']['children']['postcode']['value'] = $locationPostcode;

        $result['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shipping-address-fieldset']['children']['telephone']['validation'] = ['required-entry' => true, 'max_text_length' => 255, 'min_text_length' => 1];

        return $result;
    }
}
