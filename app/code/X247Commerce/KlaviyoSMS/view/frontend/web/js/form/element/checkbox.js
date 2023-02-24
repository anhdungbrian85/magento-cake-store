define([
    'Magento_Ui/js/form/element/abstract',
    'jquery',
    'mage/url'
], function (Abstract, $, url) {
    'use strict';

    return Abstract.extend({
        onUpdate: function () {
            this.bubble('update', this.hasChanged());
            console.log(this.value());
            console.log(this.id);
            $.ajax({
                url: url.build('x247commerce_klaviyo/quote/update'),
                data: {
                    'value': (this.value()) ? 1 : 0,
                    'input_name': this.id
                },
                type: "POST",
                dataType: 'json'
            }).done(function (data) {
            });
            this.validate();
        },

    });
});
