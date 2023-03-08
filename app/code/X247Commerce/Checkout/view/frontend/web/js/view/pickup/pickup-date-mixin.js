/**
 * @copyright  2023 247Commerce
 */

define([
	'jquery',
	'Amasty_StorePickupWithLocator/js/model/pickup/pickup-data-resolver',
    'locationContext',
    'uiRegistry',
	'mage/translate'

], function (
	$,
	pickupDataResolver,
    locationContext,
    registry
) {
    'use strict';

    var mixin = {
 
        /**
         * Get the first store work day
         *
         * @param {Object} store
         * @return {Date | * | Object.Date|null|Date}
         */
        getFirstPickupDate: function (store) {
            var minPickupDate = this.minPickupDateTime.asDateTimeObject,
                index;

            if (!store.schedule_id) {
                this.storeScheduleSelected(false);

                return this._getDefaultFirstPickupDate();
            }

            this.storeScheduleSelected(true);

            // if (this.getDataFromCache && this.restrictDates(new Date(this.dateFromCache))[0]) {
            //     this.getDataFromCache = false;

            //     return new Date(this.dateFromCache);
            // }

            if (!this._isScheduleValid(store.schedule_id)) {
                return null;
            }

            // break loop after the 31st iteration
            index = 0;

            while (!this.restrictDates(minPickupDate)[0] && index < 32) {
                minPickupDate.setDate(minPickupDate.getUTCDate() + 1);
                index++;
            }

            if (index >= 32) {
                return null;
            }

            return minPickupDate;
        },
         /**
         * Set the first store work day to date field
         *
         * @param {Object} store
         * @return {void}
         */
        setDateToFirstPickupDate: function (store) {
            var firstPickupDate = this.getFirstPickupDate(store);
            console.log(firstPickupDate)
            this.firstPickupDate = firstPickupDate;

            // This is direct access to the element because change of value does not trigger change of datepicker input
            $('#' + this.uid).datepicker('setDate', firstPickupDate);
            this.onValueChange(firstPickupDate);
        },



    };

    return function (target) {
        return target.extend(mixin);
    }
});
