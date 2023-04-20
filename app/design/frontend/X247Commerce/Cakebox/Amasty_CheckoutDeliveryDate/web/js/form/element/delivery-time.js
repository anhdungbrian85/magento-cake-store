/**
 * Delivery Date Calendar Element View
 */
define([
    'ko',
    'jquery',
    'Magento_Ui/js/form/element/select',
    'mage/translate',
    'Amasty_StorePickupWithLocator/js/model/pickup'
], function (ko, $, AbstractField, $t, pickup) {
    'use strict';

    return AbstractField.extend({
        defaults: {
            labelDelivery: ko.computed(function(){
                return pickup.isPickup() == true ? $t('Pickup Time') : $t('Delivery Time');
            })
        },
    });
})