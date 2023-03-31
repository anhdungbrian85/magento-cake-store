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
                    text: $.mage.__('Closexx'),
                    class: '',
                    click: function () {
                        this.closeModal();
                    }
                }]
            };
            var popup = modal(options, $('#custom-delivery-popup-modal'));
            $.ajax({
                url: urlBuilder.build('deliverypopup'),
                method: "POST",
                dataType: "json",
                success: function(res) {
                    if (res.showPopup && $('#custom-delivery-popup-modal').length) {
                        $('#custom-delivery-popup-modal').modal('openModal');
                    }
                }
            })
        },
    });
    return $.x247.deliverypopup;
});