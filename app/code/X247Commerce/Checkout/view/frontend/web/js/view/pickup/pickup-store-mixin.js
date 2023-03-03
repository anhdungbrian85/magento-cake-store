/**
 * @copyright  2023 247Commerce
 */

define([
	'jquery',
	'Amasty_StorePickupWithLocator/js/model/pickup/pickup-data-resolver',
    'locationContext',
	'mage/translate'
], function (
	$,
	pickupDataResolver,
    locationContext
) {
    'use strict';

    var mixin = {
 
        preselectStoreLocationPickup: function() {
            if (locationContext.deliveryType() == 0 && locationContext.storeLocationId()) {
                pickupDataResolver.storeId(locationContext.storeLocationId())
            }
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
