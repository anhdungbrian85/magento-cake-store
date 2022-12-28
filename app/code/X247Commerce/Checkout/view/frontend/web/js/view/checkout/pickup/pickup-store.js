define([
    'uiComponent',
    'ko',
    'jquery',
    'Amasty_StorePickupWithLocator/js/model/pickup',
    'Amasty_StorePickupWithLocator/js/model/pickup/pickup-data-resolver',
], function (Component, ko, $, pickup, pickupDataResolver) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'X247Commerce_Checkout/checkout/pickup/pickup-store',
            visiblePickupForm: false,
            visible: true,
        },

        initObservable: function () {
            this._super()
                .observe('visiblePickupForm visible');

            // pickup.isPickup.subscribe(this.pickupStateObserver, this);
            this.showChooseLocation();
            return this;
        },

        showChooseLocation: function () {
            if (pickupDataResolver.storeId() || window.storeLocationData.store_location_id_selected) {
                this.visible(false);
            }
        },

        pickupStateObserver: function (isActive) {
            this.visiblePickupForm(!isActive);
        },

        moveMapContainerDelivery: function () {
            $("#map-container-delivery-popup").detach().appendTo('#pickup-store-wrapper')
        },
    });
});
