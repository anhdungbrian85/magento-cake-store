define([
    'jquery',
    'uiElement',
    'Amasty_StorePickupWithLocator/js/model/pickup',
    'Amasty_StorePickupWithLocator/js/model/pickup/pickup-data-resolver',
    'Magento_Checkout/js/model/quote',
    'mage/translate'
], function ($, Element, pickup, pickupDataResolver, quote) {
    'use strict';

    return Element.extend({
        defaults: {
            visible: false,
            displayTitle: 1,
            datePickup: '',
            timePickup: '',
            template: 'X247Commerce_Checkout/checkout/pickup/shipping-details',
            storeDetailsPlaceholder: $.mage.__('Please, choose a store where you would like to pick up your order'),
        },

        initObservable: function () {
            this._super()
                .observe('visible datePickup timePickup')
                .observe({ storeDetails: this.storeDetailsPlaceholder });

            pickup.isPickup.subscribe(this.pickupStateObserver, this);

            return this;
        },
        shippingAddressDetail: function () {

            return quote.shippingAddress();
        },
        /**
         * @param {Boolean} isActive
         * @returns {void}
         */
        pickupStateObserver: function (isActive) {
            this.visible(!isActive);
        }
    });
});
