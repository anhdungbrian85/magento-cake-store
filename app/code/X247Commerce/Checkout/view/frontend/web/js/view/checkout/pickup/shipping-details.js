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
        },

        getSeletedStoreData: function () {
            let locationSelectedPopup = window.storeLocationData.store_location_id_selected;

            if (this.selectedStore) {
                return this.selectedStore;
            }

            if (locationSelectedPopup && pickupDataResolver.pickupData().stores) {
                for (let i = 0; i < pickupDataResolver.pickupData().stores.length; i++) {
                    if (pickupDataResolver.pickupData().stores[i].id == locationSelectedPopup) {
                        return pickupDataResolver.pickupData().stores[i];
                    }
                }
            }
        },

        updateDetails: function () {
            let pickupData = pickupDataResolver.pickupData();
            let locationSelectedPopup = window.storeLocationData.store_location_id_selected;

            if (this.selectedStore) {
                return this.storeDetails(this.selectedStore.details);
            } else {
                if (locationSelectedPopup && pickupDataResolver.pickupData().stores) {
                    for (let i = 0; i < pickupDataResolver.pickupData().stores.length; i++) {
                        if (pickupDataResolver.pickupData().stores[i].id == locationSelectedPopup) {
                            return this.storeDetails(pickupDataResolver.pickupData().stores[i].details);
                        }
                    }
                } else {
                    return this.storeDetails(this.storeDetailsPlaceholder);
                }
            }
        },
    });
});
