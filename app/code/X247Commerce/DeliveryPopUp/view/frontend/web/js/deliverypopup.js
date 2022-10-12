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
                modalClass: 'custom-popup-modal',
                buttons: [{
                    text: $.mage.__('Close'),
                    class: '',
                    click: function () {
                        this.closeModal();
                    }
                }]
            };
            var popup = modal(options, $('#custom-popup-modal'));
            $( document ).ready(function() {
                $('#custom-popup-modal').modal('openModal');
            });     
        },
    });
    return $.x247.deliverypopup;
});