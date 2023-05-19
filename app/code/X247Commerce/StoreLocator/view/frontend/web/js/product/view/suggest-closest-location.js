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
            this._sendAjax(
                this.options.suggestClosestLocationAjaxUrl,
                this.options.currentStoreLocationId,
                this.options.currentProductType,
                data
            );
        },

        _sendAjax: function (url, currentStoreLocationId, productType, data) {
            $.ajax({
                type: 'post',
                url: url,
                data: data,
                dataType: 'json',
                success: function (result) {
                    if (productType !== 'simple') {
                        $('.closest-location_message').text('');
                        $('.closest-location_title').html('');
                    } else {
                        if (result.status === 200) {
                            let template = mageTemplate('#closest-location-title');
                            let closestLocationHtml = 'This product is out of stock at your chosen store. Please select a different location.!';
                            for (const location of result.closest_location) {
                                closestLocationHtml += template({
                                    data: {
                                        href: urlBuilder.build("x247_storelocator/product/reselectLocation") + '/location_id/' + location.amlocator_store,
                                        name: location.name
                                    }
                                });
                            }
                            $('.closest-location_message').text('');
                            $('.closest-location_title').html(closestLocationHtml);

                        } else if(result.status === 400) {
                            $('.closest-location_message').text('');
                            $('.closest-location_title').html('');
                        } else {
                            if(parseInt(currentStoreLocationId)){
                                $('.closest-location_message').text(result.message);
                                $('.closest-location_title').html('');
                            }else{
                                $('.closest-location_message').text('');
                                $('.closest-location_title').html('');
                            }
                        }
                    }
                }
            });
        }
    });

    return $.mage.suggestClosestLocation;
});
