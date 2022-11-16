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
        },

        initObservable: function () {
            this._super()
                .observe('visiblePickupForm');

            pickup.isPickup.subscribe(this.pickupStateObserver, this);
            return this;
        },

        pickupStateObserver: function (isActive) {
            this.visiblePickupForm(!isActive);
        },

        moveMapContainerDelivery: function () {
            $("#map-container-delivery-popup").detach().appendTo('#pickup-store-wrapper')
        },
    });
});
