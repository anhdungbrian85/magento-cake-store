define([
    'ko'
], function (ko) {
	'use strict';

	return {
		storeLocationId: ko.observable(window.checkoutConfig.storeLocationId),
		deliveryType: ko.observable(window.checkoutConfig.deliveryType)
	}
})