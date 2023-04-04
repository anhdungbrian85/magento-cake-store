define([
    'ko',
    'Amasty_StorePickupWithLocator/js/model/pickup/pickup-data-resolver'
], function (ko, pickupDataResolver) {
	'use strict';

	var storeLocationId = ko.computed(function() {
        return pickupDataResolver.storeId();
    });
	
	var deliveryType = ko.observable(window.checkoutConfig.deliveryType);

	var isAsda = ko.computed(function() {
        return window.checkoutConfig.asdaLocationIds.includes(storeLocationId().toString());
    });

	return {
		storeLocationId: storeLocationId,
		deliveryType: deliveryType,
		isAsda: isAsda
	}
	
})