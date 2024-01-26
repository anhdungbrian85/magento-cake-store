define([
    'ko',
    'Magento_Customer/js/customer-data'
], function (ko, customerData) {
	'use strict';

	var storeLocationId = ko.observable(window.checkoutConfig.storeLocationId);

	var deliveryType = ko.observable(window.checkoutConfig.deliveryType);

	var isAsda = ko.computed(function() {
        return window.checkoutConfig.asdaLocationIds.includes(storeLocationId().toString());
    });

	var leadDeliveryTime = ko.observable(window.checkoutConfig.initLeadDeliveryValue);

	var cartData = customerData.get('cart');

    cartData.subscribe(function (updatedCart) {
     	var items = updatedCart.items;
     	var leadDelivery = 0;
     	if (updatedCart && updatedCart.items.length) {
     		updatedCart.items.forEach(function(item){
     			leadDelivery = leadDelivery > parseInt(item.lead_delivery) ? item.lead_delivery : leadDelivery;
     		})
     		if (leadDelivery) {
     			leadDeliveryTime(leadDelivery);
     		}
     	}
    });
    
    var deliveryPostcode = ko.observable(window.checkoutConfig.deliveryPostcode);

    return {
        storeLocationId: storeLocationId,
        deliveryType: deliveryType,
        isAsda: isAsda,
        leadDeliveryTime: leadDeliveryTime,
        deliveryPostcode: deliveryPostcode
    };
})
