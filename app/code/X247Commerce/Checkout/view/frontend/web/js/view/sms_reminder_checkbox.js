define(
    [
        'ko',
        'jquery',
        'uiComponent',
        'mage/url'
    ],
    function (ko, $, Component,url) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'X247Commerce_Checkout/sms_reminder_checkbox'
            },
            initObservable: function () {

                this._super()
                    .observe({
                        isRegisterSms: ko.observable(false)
                    });
                    
                var isRegister = 0;
                self = this;
                this.isRegisterSms.subscribe(function (newValue) {
                    var linkUrls  = url.build('x247checkout/checkout/SaveInQuote');
                    if(newValue) {
                        isRegister = 1;
                    }
                    else{
                        isRegister = 0;
                    }
                    $.ajax({
                        url: linkUrls,
                        data: {isRegisterSms : isRegister},
                        type: "POST",
                        dataType: 'json'
                    }).done(function (data) {
                        console.log('success');
                    });
                });
                return this;
            }
        });
    }
);