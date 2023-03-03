define([
    'jquery',
    'uiElement',
    'Amasty_StorePickupWithLocator/js/model/pickup',
    'Amasty_StorePickupWithLocator/js/model/pickup/pickup-data-resolver',
    'mage/translate'
], function ($, Element, pickup, pickupDataResolver) {
    'use strict';

    return Element.extend({
        defaults: {
            visible: false,
            displayTitle: 1,
            datePickup: '',
            timePickup: '',
            template: 'X247Commerce_Checkout/checkout/pickup/store-details',
            storeDetailsPlaceholder: $.mage.__('Please, choose a store where you would like to pick up your order')
        },

        initObservable: function () {
            this._super()
                .observe('visible datePickup timePickup')
                .observe({ storeDetails: this.storeDetailsPlaceholder });

            pickup.isPickup.subscribe(this.pickupStateObserver, this);
            pickupDataResolver.storeId.subscribe(this.onChangeStore, this);
            this.onChangeStore();

            return this;
        },

        onChangeStore: function () {
            this.selectedStore = pickupDataResolver.getCurrentStoreData();
            this.updateDetails();
        },

        /**
         * @param {Boolean} isActive
         * @returns {void}
         */
        pickupStateObserver: function (isActive) {
            if (isActive) {
                this.updateDetails();
            }

            this.visible(isActive);
        },

        updateDetails: function () {
            if (this.selectedStore) {
                this.storeDetails(this.selectedStore.details);
            } else {
                this.storeDetails(this.storeDetailsPlaceholder);
            }
        },

        getSeletedStoreData: function () {
            if (this.selectedStore) {
                return this.selectedStore;
            }
        }
    });
});
