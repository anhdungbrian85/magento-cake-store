define([
    'Magento_Ui/js/grid/listing'
], function (Collection) {
    'use strict';

    return Collection.extend({
        defaults: {
            template: 'X247Commerce_Sales/ui/grid/listing'
        },
        getRowClass: function (row) {
        
            if(row.increment_id.includes("COL")) {
                return 'collection';
            } else if(row.increment_id.includes("DEL")) {
                return 'delivery';
            } else if(row.increment_id.includes("KIO")) {
                return 'kiosks';
            } else {
                return 'not-specified';
            }
        }
    });
});