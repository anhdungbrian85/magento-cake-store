/**
 * @copyright  2023 247Commerce
 */

define([
	'jquery',
	'Amasty_StorePickupWithLocator/js/model/pickup/pickup-data-resolver',
    'locationContext',
    'uiRegistry',
    'Amasty_StorePickupWithLocator/js/model/pickup',
	'mage/translate',
    

], function (
	$,
	pickupDataResolver,
    locationContext,
    registry,
    pickup
) {
    'use strict';

    var mixin = {

        preselectStoreLocationPickup: function() {
            var self = this;
            // this.value = locationContext.storeLocationId();
            if (locationContext.deliveryType()  == 0) {
                pickupDataResolver.storeId(locationContext.storeLocationId());
                locationContext.storeLocationId(locationContext.storeLocationId());
                $('#' + self.uid).val(locationContext.storeLocationId()).trigger('change');
            }

        },
        onChangeStore: function (storeId) {
            pickupDataResolver.storeId(storeId);
            locationContext.storeLocationId(storeId)
        },
    };

    return function (target) {
        return target.extend(mixin);
    }
});
