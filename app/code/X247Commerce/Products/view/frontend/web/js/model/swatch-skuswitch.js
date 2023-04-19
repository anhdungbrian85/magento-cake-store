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
                    currentProductSku : simpleSku,
                    currentProductType: window.currentProductType
                };

                $.ajax({
                    type: 'post',
                    url: window.suggestClosestLocationAjaxUrl,
                    data: data,
                    dataType: 'json',
                    success: function (result) {
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
                            $('.closest-location_title').html(closestLocationHtml);
                            $('.closest-location_message').text('');
                        } else if(result.status === 400) {
                            $('.closest-location_message').text('');
                            $('.closest-location_title').html('');
                        } else {
                            $('.closest-location_message').text(result.message);
                            $('.closest-location_title').html('');
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
