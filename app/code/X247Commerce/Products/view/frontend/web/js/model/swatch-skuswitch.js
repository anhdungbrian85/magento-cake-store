define([
    'jquery',
    'mage/utils/wrapper',
    'mage/template',
    'mage/url'
], function ($, wrapper, mageTemplate, urlBuilder) {
    'use strict';

    return function(targetModule){
        var updatePrice = targetModule.prototype._UpdatePrice;
        targetModule.prototype.configurableSku = $('div.product-info-main .sku .value').html();
        var updatePriceWrapper = wrapper.wrap(updatePrice, function(original){
            var allSelected = true;
            for(var i = 0; i<this.options.jsonConfig.attributes.length;i++){
                if (!$('div.product-info-main .product-options-wrapper .swatch-attribute.' + this.options.jsonConfig.attributes[i].code).attr('data-option-selected')){
                    allSelected = false;
                }
            }
            var simpleSku = this.configurableSku;
            if (allSelected){
                var products = this._CalcProducts();
                simpleSku = this.options.jsonConfig.skus[products.slice().shift()];
                let data = {
                    currentProductSku : window.currentProductSku,
                    currentProductType: window.currentProductType
                };

                $.ajax({
                    type: 'post',
                    url: window.suggestClosestLocationAjaxUrl,
                    data: data,
                    dataType: 'json',
                    success: function (result) {
                        if (result.status === 200) {
                            if (result.closest_location.amlocator_store === window.currentStoreLocationId) {
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
            $('div.product-info-main .sku .value').html(simpleSku);
              return original();
        });

        targetModule.prototype._UpdatePrice = updatePriceWrapper;
        return targetModule;
    };
});
