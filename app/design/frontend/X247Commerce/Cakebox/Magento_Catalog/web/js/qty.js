define([
    'jquery',
    'domReady!'
], function($){
    'use strict';
    $.widget('cakebox.qty', {
        _create: function() {
            let $widget = this;
            var element = $widget.element;

          var qtyElement = element.find('.qty'),
               inc = qtyElement.find( '.plus' ),
               dec = qtyElement.find( '.minus' ),
               data = false,
               max = 1000,
               min = 1;
               if ( qtyElement.find('input[name=qty]').data('validate') ) {
                    data = qtyElement.find('input[name=qty]').data('validate')['validate-item-quantity'];
                    max = data.maxAllowed ? data.maxAllowed : 1000;
                    min = data.minAllowed ? data.minAllowed : 1;
               }
          inc.on( 'click', function (e) {
               var qty = $(this).siblings('input').val();
               if ( qty < max ) {
                    qty++;
               } else {
                    qty = max;
               }

               $(this).siblings('input').val(qty);
          } );
          dec.on( 'click', function (e) {
               var qty = $(this).siblings('input').val();
               if ( qty > min ) {
                    qty--;
               } else {
                    qty = min;
               }

               $(this).siblings('input').val(qty);
          } );
        }
    });

    return $.cakebox.qty;
});
