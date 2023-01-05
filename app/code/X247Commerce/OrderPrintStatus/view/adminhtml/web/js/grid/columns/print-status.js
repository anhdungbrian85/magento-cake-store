define([
    'Magento_Ui/js/grid/columns/column',
    'jquery',
    'uiRegistry'
], function (Column, $, registry) {
    'use strict';

    return Column.extend({
        initialize: function () {
            this._super();
        },
        
        getPrintedLabel: function(printStatus) {
            return printStatus == 1 ? "Printed" : 'Not Printed'
        },

        getPrintedClass: function(printStatus) {
            return printStatus == 1 ? "printed" : 'not-printed'
        },

        getActionLabel: function(printStatus) {
            return printStatus == 1 ? "Undo" : 'Change'
        },
       
        togglePrintStatusHandler:function(row) {

            $.ajax({
                url: window.urlUpdatePrintStatus,
                method: 'post',
                dataType: 'json',
                showLoader: true,
                data: {
                    order_id: row.entity_id,
                    current_print_status: row.print_status
                },
                success: function(res) {
                    registry.get('sales_order_grid.sales_order_grid.sales_order_columns')
                        .source
                        .reload({'refresh': true});
                }
            })
        },
        /**
         * Get field handler per row.
         *
         * @param {Object} row
         * @returns {Function}
         */
        getFieldHandler: function (row) {
            return this.togglePrintStatusHandler.bind(this, row);
        }
    });
});
