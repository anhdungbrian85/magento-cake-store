define([
    'ko',
    'jquery',
    'Magento_Ui/js/form/element/select',
    'Magento_Customer/js/customer-data',
    'Amasty_StorePickupWithLocator/js/model/pickup',
    'Amasty_StorePickupWithLocator/js/model/pickup/pickup-data-resolver',
    'Amasty_StorePickupWithLocator/js/view/pickup/pickup-date'
], function (ko, $, Component, customerData, pickup, pickupDataResolver) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'X247Commerce_Checkout/secure-time-popup',
            options: []
        },

        visibleComputed: ko.pureComputed(function () {
            var currentTime = new Date().valueOf();
            return Boolean(pickupDataResolver.secureTimeData() > currentTime);
        }),

        initialize: function () {
            this._super();
            return this;
        },

        initConfig: function () {
            this._super();
            this.visible = this.visibleComputed();
            return this;
        },

        initObservable: function () {
            this._super();
            this.visibleComputed.subscribe(this.visible);
            return this;
        },

        getContent: function () {
            var minutes = 0;
            var seconds = 0;
            // var x = setInterval(function() {
            //     var now = new Date().valueOf();
            //     console.log(now);
            //     var distance = pickupDataResolver.secureTimeData() - now;
            //     console.log('secureTimeData', pickupDataResolver.secureTimeData());
            //     console.log('distance', distance);
            //     minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            //     seconds = Math.floor((distance % (1000 * 60)) / 1000);
            //     console.log('minutes: ', minutes);
            //     console.log('seconds: ', seconds);
            //     if (distance < 0) {
            //         clearInterval(x);
            //     }
            // }, 1000);

            return 'To secure your order time slot confirm within ' + minutes + ' min ' + seconds + ' sec';
        }
    });
});
