/**
 * Copyright Â© Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'mage/url',
    'Magento_Ui/js/model/messageList',
    'mage/translate',
    'Amasty_CheckoutStyleSwitcher/js/model/amalert',
], function (url, globalMessageList, $t, alert) {
    'use strict';

    return {
        /**
         * @param {Object} response
         * @param {Object} messageContainer
         */
        process: function (response, messageContainer) {
            var error;
            messageContainer = messageContainer || globalMessageList;
            console.log(globalMessageList)
            if (response.status == 401) { //eslint-disable-line eqeqeq
                this.redirectTo(url.build('customer/account/login/'));
            } else {
                try {
                    error = JSON.parse(response.responseText);
                } catch (exception) {
                    error = {
                        message: $t('Something went wrong with your request. Please try again later.')
                    };
                }
                if(error.message == $t('We do not yet deliver to that area. Please arrange to collect in-store or use another delivery address !')){
                    alert({ content: error.message });
                }else{
                    messageContainer.addErrorMessage(error);
                }
            }
        },

        /**
         * Method to redirect by requested URL.
         */
        redirectTo: function (redirectUrl) {
            window.location.replace(redirectUrl);
        }
    };
});