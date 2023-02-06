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
        }
    });
});
