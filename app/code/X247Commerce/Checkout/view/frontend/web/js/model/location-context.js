define([
    'ko'
], function (ko) {
	'use strict';

	var storeLocationId = ko.observable(window.checkoutConfig.storeLocationId);
	
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