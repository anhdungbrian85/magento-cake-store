/**
 * Pickup Options Component
 */
define([
    'underscore',
    'jquery',
    'ko',
    'uiComponent',
    'matchMedia',
    'Amasty_StorePickupWithLocator/js/model/pickup',
    'Amasty_StorePickupWithLocator/js/model/pickup/pickup-data-resolver'
], function (
    _,
    $,
    ko,
    Component,
    mediaCheck,
    pickup,
    pickupDataResolver
) {
    'use strict';

    var storeCurbsideEnabled = ko.observable();

    return Component.extend({
        defaults: {
            template: 'Amasty_StorePickupWithLocator/pickup/pickup-options',
            isCart: false,
            curbsideChecked: false,
            curbsideCommentValue: '',
            ignoreTmpls: {
                curbsideChecked: true
            },
            links: {
                curbsideChecked: '${ $.provider }:${ $.dataScope }.curbside_state'
            }
        },
        mediaBreakpoint: '(min-width: 768px)',
        visibleComputed: ko.pureComputed(function () {
            return Boolean(false);
        }),
        firstLoad: true,
        selectors: {
            curbsideConditions: '[data-ampickup-js="curbside-conditions"]'
        },
    });
});
