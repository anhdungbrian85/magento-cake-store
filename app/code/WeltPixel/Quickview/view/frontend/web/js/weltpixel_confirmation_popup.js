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
            this.wpConfirmationPopup = customerData.get('wp_confirmation_popup');

            this.messages = customerData.get('messages');
            this.productAddedEvent = ko.computed(function()  {
               return [ that.wpConfirmationPopup(), that.messages() ];
            });
            
            $(document).on('ajax:addToCart', function(e, data) {

                if (!data.response.length) {
                    let confirmation_popup_content = that.wpConfirmationPopup();
                    let parentBody = window.parent.document.body;
                    console.log(confirmation_popup_content.confirmation_popup_content)
                    $('<div />').html(confirmation_popup_content.confirmation_popup_content)
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
