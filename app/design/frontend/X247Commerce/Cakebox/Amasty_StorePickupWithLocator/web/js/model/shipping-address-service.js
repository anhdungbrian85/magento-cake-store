define([
    'jquery',
    'underscore',
    'uiRegistry',
    'Amasty_StorePickupWithLocator/js/model/store/address',
    'Magento_Checkout/js/action/select-shipping-address',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/address-converter',
    'Magento_Checkout/js/model/checkout-data-resolver',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/model/shipping-service'
], function (
    $,
    _,
    registry,
    storeAddress,
    selectShippingAddress,
    quote,
    addressConverter,
    checkoutDataResolver,
    checkoutData,
    shippingService
) {
    'use strict';

    return {
        /**
         * Stores address typed by customer for switch it back when pickup is deactivated
         */
        nonStoreAddress: null,

        /**
         * Resolve shipping and billing address.
         * Set pickup store address as shipping address. Or Restrict use shipping address as billing.
         *
         * @param {Object|null} store
         */
        selectStoreAddress: function (store) {
            var address = quote.shippingAddress(),
                storeAddressAsShipping;

            if (address && address.getType() !== 'store-pickup-address') {
                console.log('selectStoreAddress::address', address);
                this.nonStoreAddress = address;
            }

            if (!store) {
                return;
            }

            storeAddressAsShipping = new storeAddress(store);
            console.log('selectStoreAddress::storeAddressAsShipping', storeAddressAsShipping);
            //are store address should be set to provider?
            if (!this._isAddressCanBeSelected(this.nonStoreAddress)) {
                console.log('selectStoreAddress::_isAddressCanBeSelected -> storeAddressAsShipping', storeAddressAsShipping);
                this.setAddressToProvider(storeAddressAsShipping);
            }

            if (shippingService.isLoading()) {
                // fix disabled shipping radio button (do not update shipping while loading)
                shippingService.isLoading.subscribe(function (isLoading) {
                    if (!isLoading) {
                        this.dispose();
                        console.log('selectStoreAddress::Not isLoading', storeAddressAsShipping);
                        selectShippingAddress(storeAddressAsShipping);
                    }
                });
            } else {
                console.log('selectStoreAddress::isLoading', storeAddressAsShipping);
                selectShippingAddress(storeAddressAsShipping);
            }
        },

        /**
         * Reset Shipping address.
         * Remove store address
         */
        resetAddress: function () {
            console.log('resetAddress::nonStoreAddress', this.nonStoreAddress);
            if (this.nonStoreAddress) {
                if (this._isAddressCanBeSelected(this.nonStoreAddress)) {
                    console.log('resetAddress::isAddressCanBeSelected');
                    checkoutData.setSelectedShippingAddress(this.nonStoreAddress.getKey());
                } else {
                    console.log('resetAddress::Not isAddressCanBeSelected');
                    this.nonStoreAddress.city = '';
                    this.nonStoreAddress.countryId = '';
                    this.nonStoreAddress.postcode = '';
                    this.nonStoreAddress.region = '';
                    this.nonStoreAddress.telephone = '';
                    this.nonStoreAddress.street = [];
                    this.setAddressToProvider(this.nonStoreAddress);
                }
            }

            this._silentAddressReset();
            checkoutDataResolver.resolveShippingAddress();
            checkoutDataResolver.resolveBillingAddress();
        },

        /**
         * Is address can be selected as address list option
         *
         * @param {Object} address
         * @returns {Boolean}
         * @private
         */
        _isAddressCanBeSelected: function (address) {
            return address && address.customerAddressId;
        },

        /**
         * Is form "inline" then address data should be set to provider
         * for display in fields and properly validate.
         * Convert address from quote to form and and set it to provider
         *
         * @param {Object} address
         */
        setAddressToProvider: function (address) {
            var shippingAddressData = this.convertQuoteToFormAddress(address);

            checkoutData.setShippingAddressFromData(shippingAddressData);
            registry.get('checkoutProvider').set(
                'shippingAddress',
                shippingAddressData
            );
        },

        /**
         * Remove current shipping address from quote for properly run resolver.
         * update value without notify subscribers.
         *
         * @private
         */
        _silentAddressReset: function () {
            var notifySubscribers;
            console.log('_silentAddressReset');
            if (!_.isUndefined(quote.shippingAddress._latestValue)) {
                console.log('_silentAddressReset::Not isUndefined');
                quote.shippingAddress._latestValue = null;
            } else {
                console.log('_silentAddressReset::isUndefined');
                notifySubscribers = quote.shippingAddress.notifySubscribers;
                quote.shippingAddress.notifySubscribers = function () {};
                quote.shippingAddress(null);
                quote.shippingAddress.notifySubscribers = notifySubscribers;
            }
        },

        /**
         * Convert and prepare address from quote to form (source)
         *
         * @param {Object} modelAddress
         * @returns {*|{}}
         */
        convertQuoteToFormAddress: function (modelAddress) {
            var addressData, customAttributes, inc;

            if (modelAddress.customAttributes) {
                customAttributes = {};
                _.each(modelAddress.customAttributes, function (attribute, key) {
                    if (_.isObject(attribute) && attribute.hasOwnProperty('attribute_code')) {
                        customAttributes[attribute.attribute_code] = attribute.value;
                    } else if (!_.isNumber(key)) {
                        customAttributes[key] = attribute;
                    }

                    delete modelAddress.customAttributes[key];
                });

                delete modelAddress.customAttributes;
            }

            addressData = addressConverter.quoteAddressToFormAddressData(modelAddress);

            _.each(addressData, function (attribute, key) {
                // with null or undefined a field validation can broke
                if (attribute === null || _.isUndefined(attribute)) {
                    switch (key) {
                        case 'email':
                        case 'company':
                        case 'telephone':
                        case 'fax':
                        case 'postcode':
                        case 'city':
                        case 'firstname':
                        case 'lastname':
                        case 'middlename':
                        case 'prefix':
                        case 'suffix':
                            addressData[key] = '';
                            break;
                        case 'street':
                            addressData[key] = {};
                            break;
                        default:
                            delete addressData[key];
                    }
                }

                if (key === 'street') {
                    for (inc = 0; inc < 4; inc++) {
                        // eslint-disable-next-line max-depth
                        if (addressData[key][inc] === null || _.isUndefined(addressData[key][inc])) {
                            addressData[key][inc] = '';
                        }
                    }
                }
            });
            addressData['custom_attributes'] = customAttributes;

            return addressData;
        }
    };
});
