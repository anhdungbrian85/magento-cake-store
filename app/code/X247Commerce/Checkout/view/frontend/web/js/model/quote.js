// Checkout layout options model
define([
    'mage/utils/wrapper'
], function (wrapper) {
    'use strict';

    var quoteData = window.checkoutConfig.quoteData;

    return function (MageQuote) {
        MageQuote.getDeliveryType = wrapper.wrapSuper(MageQuote.getDeliveryType, function () {
        	return quoteData['delivery_type'];
        });
        return MageQuote;
    };
});
