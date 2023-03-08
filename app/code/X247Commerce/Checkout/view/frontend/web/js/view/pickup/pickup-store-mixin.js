/**
 * @copyright  2023 247Commerce
 */

define([
	'jquery',
	'Amasty_StorePickupWithLocator/js/model/pickup/pickup-data-resolver',
    'locationContext',
    'uiRegistry',
	'mage/translate'

], function (
	$,
	pickupDataResolver,
    locationContext,
    registry
) {
    'use strict';

    var mixin = {
 
        preselectStoreLocationPickup: function() {
            if (locationContext.deliveryType() == 0 && locationContext.storeLocationId()) {
                pickupDataResolver.storeId(locationContext.storeLocationId())
            }
            let pickUpComponent = registry.get('checkout.steps.shipping-step.shippingAddress.amstorepickup.am_pickup_date');
            //trigger
            pickUpComponent.onChangeStore();
        },
        onChangeStore: function (storeId) {
            pickupDataResolver.storeId(storeId);
            // as we need Store Location Id data even using other delivery methods
            locationContext.storeLocationId(storeId)
        },

    };

    return function (target) {
        return target.extend(mixin);
    }
});
