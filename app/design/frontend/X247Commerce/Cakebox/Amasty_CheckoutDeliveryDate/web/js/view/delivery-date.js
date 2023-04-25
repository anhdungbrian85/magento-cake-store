/**
 * Delivery Date View
 */
define([
    'jquery',
    'underscore',
    'uiComponent',
    'Amasty_CheckoutCore/js/view/utils',
    'Amasty_CheckoutDeliveryDate/js/action/update-delivery',
    'Amasty_CheckoutDeliveryDate/js/model/delivery',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Amasty_CheckoutCore/js/view/checkout/datepicker'
], function (
    $,
    _,
    Component,
    viewUtils,
    updateAction,
    deliveryService,
    paymentValidatorRegistry
) {
    'use strict';

    function formatDate(dateOrig) {
        if (dateOrig) {
            const toFragments = dateString => dateString ? dateString.split(/[-/]/) : dateString;
            const dateTo_mmddyyyy = ([date, month, year], divider = "/") => `${month}${divider}${date}${divider}${year}`;
            return dateTo_mmddyyyy(toFragments(dateOrig));
        }
        return dateOrig;
    }       

    return Component.extend({
        defaults: {
            template: 'Amasty_CheckoutDeliveryDate/delivery_date',
            listens: {
                'update': 'update'
            }
        },
        isLoading: deliveryService.isLoading,
        _requiredFieldSelector: '.amcheckout-delivery-date .field._required :input:not(:button)',

        initialize: function () {
            this._super();

            var self = this,
                validator = {
                    validate: self.validate.bind(self)
                };

            paymentValidatorRegistry.registerValidator(validator);

            return this;
        },

        update: function () {
            var data,
                deliveryElem = $('[name="amcheckoutDelivery.date"] input[name="date"]');
            if (this.validate()) {
                data = this.source.get('amcheckoutDelivery');
                data['date'] = formatDate(deliveryElem.val()); // update formated date
                updateAction(data);
            }
        },

        validate: function () {
            var validationResult;

            this.source.set('params.invalid', false);
            this.source.trigger('amcheckoutDelivery.data.validate');

            if (this.source.get('params.invalid')) {
                return false;
            }

            validationResult = true;

            this.elems().forEach(function (item) {
                if (item.validate().valid === false) {
                    validationResult = false;

                    return false;
                }

                return true;
            });

            return validationResult;
        },

        getDeliveryDateName: function () {
            return viewUtils.getBlockTitle('delivery');
        }
    });
});
