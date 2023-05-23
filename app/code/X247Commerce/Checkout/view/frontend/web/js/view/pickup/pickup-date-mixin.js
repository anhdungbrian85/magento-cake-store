/**
 * @copyright  2023 247Commerce
 */

define([
	'jquery',
	'Amasty_StorePickupWithLocator/js/model/pickup/pickup-data-resolver',
    // 'Amasty_StorePickupWithLocator/js/view/pickup/pickup-time',
    'locationContext',
    'uiRegistry',
	'mage/translate'

], function (
	$,
	pickupDataResolver,
    // pickupTime,
    locationContext,
    registry
) {
    'use strict';

    var mixin = {

        /**
         * Set the first store work day to date field
         *
         * @param {Object} store
         * @return {void}
         */
        setDateToFirstPickupDate: function (store) {
            let firstPickupDate = this.getFirstPickupDate(store);

            this.firstPickupDate = firstPickupDate;

            // This is direct access to the element because change of value does not trigger change of datepicker input
            $('#' + this.uid).datepicker('setDate', firstPickupDate);
            this.onValueChange(firstPickupDate);
        },
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
                selectedStoreData = pickupDataResolver.getCurrentStoreData(),
                storeDateTime,
                isToday,
                minPickupDateWithoutTime,
                dateWithoutTime,
                daySchedule,
                scheduleArray,
                timeIntervals = pickupDataResolver.getTimeIntervalsByScheduleId(selectedStoreData.schedule_id),
                currentDayName = this.weekDays[date.getDay()];

            if (!selectedStore) {
                return [false, ''];
            }

            storeDateTime = this.currentStoreDateTime.asDateTimeObject;

            isToday = this.isDateIsStoreToday(date, storeDateTime);

            if (timeIntervals[currentDayName] && isToday && selectedStoreData.schedule_id) {
                timeIntervals = this.restrictTimeIntervals(timeIntervals[currentDayName]);
            }

            if (isToday && timeIntervals.length == 0) {
                return [false, ''];
            }

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

            let today = storeDateTime,
                tomorrow = new Date();
                tomorrow.setDate(today.getDate() + 1);
            var isTomorrow = tomorrow.toDateString() === date.toDateString();

            if (isTomorrow && locationContext.isAsda()) {

                var currentStore = pickupDataResolver.getCurrentStoreData() || {},
                    currentStoreTime = currentStore.current_timezone_time,
                    minPickupTime = currentStoreTime + parseInt(locationContext.leadDeliveryTime())*3600,
                    asdaCutOffTimeTmr = new Date(storeDateTime.getFullYear(), storeDateTime.getUTCMonth(), storeDateTime.getUTCDate(), 16),
                    cutOffTimeToInt = Date.parse(asdaCutOffTimeTmr)/1000 - (today.getTimezoneOffset() * 60);

                return [minPickupTime < cutOffTimeToInt, ''];
            }

            return [true, ''];
        },
        restrictTimeIntervals: function (intervals) {
            var currentStore = pickupDataResolver.getCurrentStoreData() || {},
                currentStoreTime = currentStore.current_timezone_time,
                filteredIntervals = [];

            for(let i = 0; i < intervals.length; i++) {
                if (intervals[i].fromInUnix > (currentStoreTime + parseInt(locationContext.leadDeliveryTime())*3600)) {
                    filteredIntervals.push(intervals[i]);
                }
            }
            // filteredIntervals = intervals.filter(function (item) {
            //     return item.fromInUnix > (currentStoreTime + parseInt(locationContext.leadDeliveryTime())*3600)
            //         // && item.toInUnix <= this.sameDayCutoffTime;
            // }.bind(this));

            return filteredIntervals;
        },
    };

    return function (target) {
        return target.extend(mixin);
    }
});
