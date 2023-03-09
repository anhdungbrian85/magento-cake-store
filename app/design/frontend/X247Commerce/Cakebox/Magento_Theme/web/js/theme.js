/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'owl',
    'mage/smart-keyboard-handler',
    'mage/mage',
    'mage/ie-class-fixer',
    'domReady!'
], function ($, owlCarousel, keyboardHandler) {
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

    $('.shop-now-block').addClass('owl-carousel').owlCarousel({
        stagePadding: 30,
        loop: true,
        margin: 15,
        responsiveClass:true,
        responsive:{
            0:{
                items: 2,
                nav: false,
                dots: false,
                margin: 10
            },
            768:{
                items: 3,
                nav: false
            },
            993:{
                items: 5,
                nav: false,
            },
            1024: {
                items:6,
                nav: false,
                margin: 35,
            }
        }
    });

    if ( $( window ).width() < 767 ) {
        $('.am-filter-items-color, .am-ranges.price-ranges').parents('.filter-options-item').hide();
        $('.swatch-layered.size_servings .swatch-option.text').each( function (){
            let value = $.trim($(this).text());
            let arrayValue = value.split(" ");
            arrayValue[0] = '<span class="size-number-value">' + arrayValue[0] + '</span>';
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
