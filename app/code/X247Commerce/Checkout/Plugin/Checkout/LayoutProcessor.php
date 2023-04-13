<?php

namespace X247Commerce\Checkout\Plugin\Checkout;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session;
use Klaviyo\Reclaim\Helper\ScopeSetting;

class LayoutProcessor
{
    protected $paymentModelConfig;
    protected $locationModel;
    protected $storeLocationContextInterface;
    protected $scopeConfig;
    protected $_customerSession;
    protected $_customerFactory;
    protected $_klaviyoScopeSetting;

    public function __construct(
        \Magento\Payment\Model\Config $paymentModelConfig,
        \Amasty\Storelocator\Model\Location $locationModel,
        \X247Commerce\Checkout\Api\StoreLocationContextInterface $storeLocationContextInterface,
        ScopeConfigInterface $scopeConfig,
        Session $customerSession,
        ScopeSetting $klaviyoScopeSetting,
        CustomerFactory $customerFactory

    )
    {
        $this->paymentModelConfig = $paymentModelConfig;
        $this->locationModel = $locationModel;
        $this->storeLocationContextInterface = $storeLocationContextInterface;
        $this->scopeConfig = $scopeConfig;
        $this->_customerSession = $customerSession;
        $this->_customerFactory = $customerFactory;
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

        if ($this->_klaviyoScopeSetting->getConsentAtCheckoutSMSIsActive()) {
            $smsConsentCheckbox = [
                'component' => 'Magento_Ui/js/form/element/abstract',
                'config' => [
                    'customScope' => 'shippingAddress.custom_attributes',
                    'template' => 'ui/form/field',
                    'elementTmpl' => 'ui/form/element/checkbox',
                    'options' => [],
                    'id' => 'kl_sms_consent',
                ],
                'dataScope' => 'shippingAddress.custom_attributes.kl_sms_consent',
                'label' => $this->_klaviyoScopeSetting->getConsentAtCheckoutSMSConsentLabelText(),
                'description' => $this->_klaviyoScopeSetting->getConsentAtCheckoutSMSConsentText(),
                'provider' => 'checkoutProvider',
                'visible' => true,
                'checked' => false,
                'validation' => [],
                'sortOrder' => $this->_klaviyoScopeSetting->getConsentAtCheckoutSMSConsentSortOrder(),
                'id' => 'kl_sms_consent',
            ];

            $address = $this->_getDefaultAddressIfSetForCustomer();

            if (!$address) {
                $result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['billing-address-form']['children']['form-fields']['children']['kl_sms_consent'] = $smsConsentCheckbox;
            }
        }

        if (!$this->scopeConfig->getValue('x247commerce_checkout/billing/enable', ScopeInterface::SCOPE_STORE)) {
            $result['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children']['afterMethods']['children']['billing-address-form']['children']['form-fields']['children']
            ['telephone']['validation'] = ['required-entry' => false, 'max_text_length' => 255, 'min_text_length' => 1];
        }

        return $result;
    }

    /**
     * Checks if logged in user has a default address set, if not returns false.
     *
     * @return Magento\Customer\Model\Address|false
     */
    public function _getDefaultAddressIfSetForCustomer()
    {
        $address = false;
        if ($this->_customerSession->isLoggedIn()) {
            $customerData = $this->_customerSession->getCustomer()->getData();
            $customerId = $customerData["entity_id"];
            $customer = $this->_customerFactory->create()->load($customerId);
            $address = $customer->getDefaultShippingAddress();
        }
        return $address;
    }
}
