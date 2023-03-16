/**
 * @copyright  2023 247Commerce
 */

define([
	'jquery',
	'Amasty_StorePickupWithLocator/js/model/pickup/pickup-data-resolver',
        'Amasty_StorePickupWithLocator/js/model/pickup',
    'locationContext',
    'uiRegistry',
	'mage/translate',
    

], function (
	$,
	pickupDataResolver,
    pickup,
    locationContext,
    registry,
) {
    'use strict';

    var mixin = {

        preselectStoreLocationPickup: function() {
            // var self = this;
            // // pickupDataResolver.updatePickupDefaultValue();
            // // this.value = locationContext.storeLocationId();
            // // if (locationContext.deliveryType()  == 0) {
                
            //     console.log(self.uid);
            //     $('#' + self.uid).val(locationContext.storeLocationId()).trigger('change');
            //     pickupDataResolver.storeId(locationContext.storeLocationId());
                
            // // }

        },
        
    };

    return function (target) {
        return target.extend(mixin);
    }
});
