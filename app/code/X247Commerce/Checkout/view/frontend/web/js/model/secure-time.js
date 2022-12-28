/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Checkout adapter for customer data storage
 */
define([
    'jquery',
    'uiRegistry'
], function ($, registry) {
    'use strict';

    var countDown;

    return {
        countDownTimer: function (pickupDateOld, pickupTimeOld) {
            if (countDown) {
                clearInterval(countDown);
            }

            if (window.timer) {
                clearInterval(window.timer);
            }

            if (pickupTimeOld) {
                var valueTimeInit = pickupTimeOld.options()[0];
            }
            
            var secureTimeEnd = new Date(new Date().getTime() + 15 * 60000),
                minutes, seconds;

            window.timer = countDown = setInterval(function() {
                var now = new Date().valueOf(),
                    distance = secureTimeEnd.valueOf() - now;

                minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                seconds = Math.floor((distance % (1000 * 60)) / 1000);
              
                $('#block-secure-time-popup_wrapper .content').text('To secure your order time slot confirm within ' + minutes + ' min ' + seconds + ' sec');
                if (minutes <= 0 && seconds <= 0) {
                    
                    if (pickupDateOld) {
                        pickupDateOld.value('');
                        $('#' + pickupDateOld.uid).datepicker('setDate', '');
                    }

                    if (valueTimeInit) {
                        pickupTimeOld.value(valueTimeInit.value);
                    }
                    
                    clearInterval(countDown);
                }
            }, 1000);
        }
    };
});
