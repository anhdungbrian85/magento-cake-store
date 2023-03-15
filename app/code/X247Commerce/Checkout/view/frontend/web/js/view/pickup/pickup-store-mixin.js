/**
 * @copyright  2023 247Commerce
 */

define([
	'jquery',
	'Amasty_StorePickupWithLocator/js/model/pickup/pickup-data-resolver',
    'locationContext',
    'uiRegistry',
    'Amasty_StorePickupWithLocator/js/model/pickup',
    'Magento_Customer/js/customer-data',
	'mage/translate'
    

], function (
	$,
	pickupDataResolver,
    locationContext,
    registry,
    pickup,
    customerData
) {
    'use strict';

    var mixin = {

        preselectStoreLocationPickup: function() {
            pickupDataResolver.updateDefaultValue();
            pickupDataResolver.storeId.valueHasMutated();
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
