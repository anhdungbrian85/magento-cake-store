define([
    'jquery',
    'Magento_Ui/js/modal/modal'
], function($, modal){
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
            $( document ).ready(function() {
                $('#custom-delivery-popup-modal').modal('openModal');
            });     
        },
    });
    return $.x247.deliverypopup;
});