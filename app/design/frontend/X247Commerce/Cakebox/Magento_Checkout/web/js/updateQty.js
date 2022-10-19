define([
    'jquery'
], function ($) {
    'use strict';
    $.widget('cakebox.updateqtycart', {
        _create: function() {
            var self = this;
            self._increaseDecInit();
        },

        _increaseDecInit: function() {
            var updateCart;
            $(document).on("click", '.qty-control.minus', function(){
                var input = $(this).closest('.qty').find('input');
                var value  = parseInt(input.val());
                if(value) input.val(value-1);
                clearTimeout(updateCart);
                updateCart = setTimeout(function(){ $('.form-cart .action.update').trigger('click'); }, 1000);
            });
            $(document).on("click", '.qty-control.plus', function(){
                var input = $(this).closest('.qty').find('input');
                var value  = parseInt(input.val());
                input.val(value+1);
                clearTimeout(updateCart);
                updateCart = setTimeout(function(){ $('.form-cart .action.update').trigger('click'); }, 1000);
            });
        }
    });

    return $.cakebox.updateqtycart;
});
