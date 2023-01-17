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
        loop: true,
        margin: 15,
        responsiveClass:true,
        center: true,
        responsive:{
            0:{
                items: 4,
                nav: false,
                dots: false,
                margin: 15
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
});
