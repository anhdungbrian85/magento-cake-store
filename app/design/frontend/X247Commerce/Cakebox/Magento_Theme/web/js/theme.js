/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/smart-keyboard-handler',
    'mage/mage',
    'mage/ie-class-fixer',
    'domReady!'
], function ($, keyboardHandler) {
    'use strict';

    $('.cart-summary').mage('sticky', {
        container: '#maincontent'
    });

    $('.panel.header > .header.links').clone().appendTo('#store\\.links');
    $('#store\\.links li a').each(function () {
        var id = $(this).attr('id');

        if (id !== undefined) {
            $(this).attr('id', id + '_mobile');
        }
    });

    keyboardHandler.apply();

    $(function() {
        setTimeout(function(){
            $(".custom-topmenu-mobile .category-top").show();
        }, 2000);
    });

    if ( $( window ).width() < 767 ) {
        $('.am-filter-items-color, .am-ranges.price-ranges').parents('.filter-options-item').hide();
        $('.swatch-layered.size_servings .swatch-option.text').each( function (){
            let value = $.trim($(this).text());
            let arrayValue = value.split(" ");
            if (arrayValue[3] == 'Cupcakes') {
                arrayValue[0] = '<span class="size-number-value">' + arrayValue[0];
                arrayValue[2] = arrayValue[2] + '</span>';
            } else {
                arrayValue[0] = '<span class="size-number-value">' + arrayValue[0] + '</span>';
            }
            $(this).html(arrayValue.join(' '));
        });
    }

    $('body').on( 'click', '.item-parent .icon-toggle', function(e) {
        e.preventDefault();
        var parentItem = $(this).parents('.item-parent'),
            submenu = parentItem.find('.submenu');
        if ( submenu.hasClass('show') ) {
            submenu.slideUp();
            submenu.removeClass('show');
            $(this).removeClass('show');
        } else {
            submenu.slideDown();
            submenu.addClass('show');
            $(this).addClass('show');
        }
    } );
});
