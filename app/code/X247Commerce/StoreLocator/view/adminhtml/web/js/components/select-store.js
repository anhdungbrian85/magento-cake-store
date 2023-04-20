define([
    'Magento_Ui/js/form/element/ui-select',
    'uiRegistry'
], function (Select, uiRegistry) {
    'use strict';
    return Select.extend({
        /**
         * Parse data and set it to options.
         *
         * @param {Object} data - Response data object.
         * @returns {Object}
         */
        setParsed: function (data) {
            var option = this.parseData(data);
            if (data.error) {
                return this;
            }
            this.options([]);
            this.setOption(option);
            this.set('newOption', option);
        },
        /**
         * Normalize option object.
         *
         * @param {Object} data - Option object.
         * @returns {Object}
         */
        parseData: function (data) {
            return {
                value: data.store_id.id,
                label: data.store_id.name
            };
        }
    });
});