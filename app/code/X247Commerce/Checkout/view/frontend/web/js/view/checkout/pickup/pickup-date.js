define([
    'Magento_Ui/js/form/element/date',
    'ko',
    'jquery',
    'uiRegistry',
    'mage/calendar'
], function (Component, ko, $, uiRegistry) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'X247Commerce_Checkout/checkout/pickup/pickup-date'
        },

        onValueChange: function (value) {
            if (value) {
                uiRegistry.get('checkout.steps.shipping-step.shippingAddress.amstorepickup.am_pickup_date').value(value);
            }
            
        },
    });
});
