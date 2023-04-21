define([
    'ko',
    'jquery',
    'weltpixel_quickview',
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'magnificPopup'
], function (ko, $, weltpixel_quickview, Component, customerData, magnificPopup) {
    'use strict';

    return Component.extend({
        /** @inheritdoc */
        initialize: function () {
            var that  = this;
            this._super();
            $(document).on('ajax:addToCart', function(e, data) {

                if (data.response.confirmation_popup_content) {
                    let confirmation_popup_content = data.response.confirmation_popup_content;
                    let parentBody = window.parent.document.body;
                    $('<div />').html(confirmation_popup_content)
                    .modal({
                        autoOpen: true,
                        modalClass: 'wp-confirmation-popup-wrapper',
                        modalCloseBtn: '.mfp-close',
                        buttons: [{
                            text: "Continue Shopping",
                            attr: {
                                'data-action': 'confirm'
                            },
                            'class': 'action primary',
                            click: function () {
                                this.closeModal();
                                $('.mfp-close', parentBody).trigger('click');
                            }
                        },
                            {
                                text: "Go To Checkout",
                                attr: {
                                    'data-action': 'cancel'
                                },
                                'class': 'action primary',
                                click: function () {
                                    parent.window.location = window.location.origin + '/checkout'
                                }
                        }],
                        
                        callbacks: {
                            beforeClose: function() {
                                $('[data-block="minicart"]').trigger('contentLoading');
                                $.ajax({
                                    url: url,
                                    method: "POST"
                                });
                            }
                        },
                    });
                }
                
            });
        }
    });
});
