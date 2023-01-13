/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
    map: {
        '*': {
            owl: 'Magento_Theme/js/owl.carousel.min',
        }
    },
    shim: {
        'Magento_Theme/js/owl.carousel.min': {
            deps: ['jquery']
        }
    }
};
