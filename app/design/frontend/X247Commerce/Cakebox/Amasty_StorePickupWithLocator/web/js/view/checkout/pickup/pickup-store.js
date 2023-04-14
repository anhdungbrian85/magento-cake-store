/**
 * Pickup Store UIElement for Checkout page
 * Nested from Main Pickup Store UIElement
 */
define([
    'Amasty_StorePickupWithLocator/js/view/pickup/pickup-store',
    'Amasty_StorePickupWithLocator/js/model/pickup/pickup-data-resolver',
    'Amasty_StorePickupWithLocator/js/model/shipping-address-service',
    'Magento_Checkout/js/action/set-shipping-information'
], function (PickupStore, pickupDataResolver, addressService, setShippingInformationAction) {
    'use strict';

    return PickupStore.extend({
        defaults: {
            visible: false,
            required: true,
            template: 'Amasty_StorePickupWithLocator/checkout/pickup/pickup-store'
        },

        storeObserver: function () {
            this._super();

            addressService.selectStoreAddress(pickupDataResolver.getCurrentStoreData());
            setShippingInformationAction()
        },

        pickupStateObserver: function (isActive) {
            this._super();

            if (!isActive) {
                addressService.resetAddress();
            }

            this.visible(isActive);
        }
    });
});
