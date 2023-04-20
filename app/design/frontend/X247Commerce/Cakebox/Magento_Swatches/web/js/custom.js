define([
    'jquery',
    'domReady!'
], function($){
    'use strict';
    $.widget('cakebox.custom', {
          _create: function() {
               let $widget = this;
               var element = $widget.element,
                    sswitch = 0;

               $('body').on( 'click', '.swatch-attribute-arrow-icon', function (e) {
                    e.preventDefault();

                    var arrow = $(this),
                        maxWidth = $('.swatch-attribute-options').width(),
                        currentAttr = $(this).parents('.swatch-attribute'),
                        width = 0;

                    currentAttr.find('.swatch-option').each( function (e) {
                         width += $(this).outerWidth();
                    } );

                    if ( ( width - maxWidth - sswitch) > 0 ) {
                         sswitch += 86;
                         if ( ( width - maxWidth - sswitch ) >= 86 ) {
                              sswitch += 86;
                         }
                    }

                    currentAttr.find('.swatch-option-lists').scrollLeft(sswitch) ;
               } );
          }
    });

    return $.cakebox.custom;
});
