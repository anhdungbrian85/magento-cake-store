define([
    'jquery'
], function($){
    'use strict';
    $.widget('mage.suggestClosestLocation', {
        _create: function() {
            let data = {};
            this._sendAjax(this.options.suggestClosestLocationAjaxUrl, data);
        },

        _sendAjax: function (url, data) {
            $.ajax({
                type: 'post',
                url: url,
                data: data,
                dataType: 'json',
                success: function (result) {
                    console.log(result);
                }
            });
        }
    });

    return $.mage.suggestClosestLocation;
});
