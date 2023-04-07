define([
    'jquery',
    'mage/template',
    'mage/url'
], function($, mageTemplate, urlBuilder){
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
                    if (result.status === 200) {
                        let template = mageTemplate('#closest-location-title');
                        let closestLocationHtml = template({
                            data: {
                                href: urlBuilder.build("x247_storelocator/product/reselectLocation") + '/location_id/' + result.closest_location.amlocator_store,
                                name: result.closest_location.name
                            }
                        });
                        $('.closest-location_title').append(closestLocationHtml);
                    } else {
                        $('.closest-location_message').text(result.message);
                    }
                }
            });
        }
    });

    return $.mage.suggestClosestLocation;
});
