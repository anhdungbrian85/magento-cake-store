/**
 * @copyright  2023 247Commerce
 */

define([
	'jquery',
	'Amasty_StorePickupWithLocator/js/model/pickup/pickup-data-resolver',
	'mage/translate'
], function (
	$,
	pickupDataResolver
) {
    'use strict';

    var mixin = {
 
        preselectStoreLocationPickup: function() {
            if (checkoutConfig.deliveryType == 0 && checkoutConfig.storeLocationId) {
                pickupDataResolver.storeId(checkoutConfig.storeLocationId)
            }
        },

    };

    return function (target) {
        return target.extend(mixin);
    }
});
