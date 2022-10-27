define([
    'jquery',
    'Magento_Ui/js/modal/modal'
], function($){
    'use strict';
    $.widget('product.inputNumber', {
        _create: function() {
            var sku = $('.input-text.product-custom-option.test-cake');
            $(document).ready(function(){
                var data = $(sku).data("validate");
                if (!("validate-digits" in data)) {
                     var newObj = {'validate-digits':true};
                     $.extend(data, newObj);
                     $(sku).attr("data-validate", JSON.stringify(data));
                }
            });
        },
    });
    return $.product.inputNumber;
});