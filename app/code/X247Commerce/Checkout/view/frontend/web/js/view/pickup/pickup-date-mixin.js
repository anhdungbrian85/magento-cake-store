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
         * Check if date is valid
         * Method returns [false, ''] if date is restricted
         * Method returns [true, ''] if date is NOT restricted
         *
         * @param {Date||String} date
         * @return {[boolean, string]}
         */
        restrictDates: function (date) {
            var selectedStore = this.selectedStore,
                storeDateTime,
                isToday,
                minPickupDateWithoutTime,
                dateWithoutTime,
                currentDayName,
                daySchedule,
                scheduleArray;

            if (!selectedStore) {
                return [false, ''];
            }

            storeDateTime = this.currentStoreDateTime.asDateTimeObject;
            isToday = this.isDateIsStoreToday(date, storeDateTime);

            minPickupDateWithoutTime = new Date(
                this.minPickupDateTime.asDateTimeObject.getUTCFullYear(),
                this.minPickupDateTime.asDateTimeObject.getUTCMonth(),
                this.minPickupDateTime.asDateTimeObject.getUTCDate()
            );

            dateWithoutTime = new Date(
                date.getFullYear(),
                date.getMonth(),
                date.getDate()
            );

            if (dateWithoutTime < minPickupDateWithoutTime) {
                return [false, ''];
            }

            if (selectedStore.schedule_id) {
                scheduleArray = pickupDataResolver.getScheduleByScheduleId(selectedStore.schedule_id);
                currentDayName = this.weekDays[date.getDay()];
                daySchedule = scheduleArray[currentDayName];

                // check current day status in Store Schedule object
                if (!+daySchedule[currentDayName + '_status']) {
                    return [false, ''];
                }
            }

            if (isToday && !this._isSameDayAllowed(daySchedule)) {
                return [false, ''];
            }

            if (isToday && locationContext.isAsda()) {
                return [false, ''];
            }

            return [true, ''];
        },
    };

    return function (target) {
        return target.extend(mixin);
    }
});
