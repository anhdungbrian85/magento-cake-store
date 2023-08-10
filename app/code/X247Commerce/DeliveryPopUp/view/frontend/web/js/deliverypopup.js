define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mage/url'
], function($, modal, urlBuilder){
    'use strict';
    $.widget('x247.deliverypopup', {
        _create: function() {

            let $widget = this;
            var options = {
                type: 'popup',
                responsive: true,
                innerScroll: true,
                clickableOverlay: false,
                modalClass: 'custom-delivery-popup-modal',
                buttons: [{
                    text: $.mage.__('Close'),
                    class: '',
                }],
                closed: function() {
                    console.log('urlBuilder_closed', urlBuilder)
                    $.ajax({
                        url: urlBuilder.build('deliverypopup/index/close'),
                        method: "POST",
                        dataType: "json",
                        success: function(res) {
                            
                        }
                    })
                }, 
                opened: function() {
                    $('html').css('height', 'auto');
                }
            };
            var popup = modal(options, $('#custom-delivery-popup-modal'));

            var currentUrl = window.location.href;
            console.log('urlBuilder_deliverypopup', urlBuilder)
            $.ajax({
                url: urlBuilder.build('deliverypopup'),
                method: "POST",
                dataType: "json",
                success: function(res) {
                    if (res.showPopup && $('#custom-delivery-popup-modal').length && !currentUrl.includes("checkout/onepage/success")) {
                        $('#custom-delivery-popup-modal').modal('openModal');
                    }
                    if ($('body').hasClass('catalog-product-view') && !res.enableAddToCart) {
                        window.preventAddToCartAction = true;
                        $("#product-addtocart-button").on('click', function(e){
                            e.preventDefault();
                            $('#custom-delivery-popup-modal').modal('openModal');
                        })
                    }

                }
            })

            


        },
    });
    return $.x247.deliverypopup;
});