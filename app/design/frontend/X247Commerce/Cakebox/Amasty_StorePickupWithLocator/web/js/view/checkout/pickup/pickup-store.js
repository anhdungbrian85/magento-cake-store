/**
 * Pickup Store UIElement for Checkout page
 * Nested from Main Pickup Store UIElement
 */
define([
    'Amasty_StorePickupWithLocator/js/view/pickup/pickup-store',
    'Amasty_StorePickupWithLocator/js/model/pickup/pickup-data-resolver',
    'Amasty_StorePickupWithLocator/js/model/shipping-address-service',
], function (PickupStore, pickupDataResolver, addressService) {
    'use strict';

    return PickupStore.extend({
        defaults: {
            visible: false,
            required: true,
            template: 'Amasty_StorePickupWithLocator/checkout/pickup/pickup-store'
        },

        storeObserver: function () {
            this._super();
            let storeAddress = {};
            if (pickupDataResolver.getCurrentStoreData()) {
                storeAddress = pickupDataResolver.getCurrentStoreData();
            } else {
                let locationSelectedPopup = window.storeLocationData.store_location_id_selected;
                if (locationSelectedPopup && pickupDataResolver.pickupData().stores) {
                    for (let i = 0; i < pickupDataResolver.pickupData().stores.length; i++) {
                        if (pickupDataResolver.pickupData().stores[i].id == locationSelectedPopup) {
                            storeAddress = pickupDataResolver.pickupData().stores[i];
                            storeAddress["firstname"] = "Pickup";
                            storeAddress["lastname"] = "Cakebox";
                        }
                    }
                }
            }
            if (document.getElementById('shipping-new-address-form')) {
                document.getElementById('shipping-new-address-form').style.display = 'none';
            }
            addressService.selectStoreAddress(storeAddress);
        },

        pickupStateObserver: function (isActive) {
            this._super();

            if (!isActive) {
                if (document.getElementById('shipping-new-address-form')) {
                    document.getElementById('shipping-new-address-form').style.display = 'block';
                }
                addressService.resetAddress();
            } else {
                console.log(document.getElementById('shipping-new-address-form'));
                if (document.getElementById('shipping-new-address-form')) {
                    document.getElementById('shipping-new-address-form').style.display = 'none';
                }
            }

            this.visible(isActive);
        }
    });
});
