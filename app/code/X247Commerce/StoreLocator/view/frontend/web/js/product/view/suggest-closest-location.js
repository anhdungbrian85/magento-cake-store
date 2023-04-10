define([
    'jquery',
    'mage/template',
    'mage/url'
], function($, mageTemplate, urlBuilder){
    'use strict';
    $.widget('mage.suggestClosestLocation', {
        _create: function() {
            let data = {
                currentProductSku : this.options.currentProductSku,
                currentProductType: this.options.currentProductType
            };
            this._sendAjax(this.options.suggestClosestLocationAjaxUrl, this.options.currentStoreLocationId, data);
        },

        _sendAjax: function (url, currentStoreLocationId, data) {
            $.ajax({
                type: 'post',
                url: url,
                data: data,
                dataType: 'json',
                success: function (result) {
                    if (result.status === 200) {
                        if (result.closest_location.amlocator_store === currentStoreLocationId) {
                            return;
                        }
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
