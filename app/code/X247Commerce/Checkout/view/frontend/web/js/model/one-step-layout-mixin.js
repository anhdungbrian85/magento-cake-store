// Checkout layout options model
define([
    'ko',
    'locationContext',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote'
], function (ko, locationContext, wrapper, quote) {
    'use strict';

    return function (oneStepLayout) {
        oneStepLayout.getBlockClassNames = wrapper.wrapSuper(oneStepLayout.getBlockClassNames, function (blockName) {
            let className = this._super(blockName);
            className += ' ' + blockName;
            if (quote.shippingMethod() && quote.shippingMethod()['carrier_code']) {
            	className += ' ' + quote.shippingMethod()['carrier_code'];
            }
            if (blockName == 'delivery') {
            	className += ' delivery-type-' + locationContext.deliveryType();
            }

            return className;
        });

        return oneStepLayout;
    };
});
