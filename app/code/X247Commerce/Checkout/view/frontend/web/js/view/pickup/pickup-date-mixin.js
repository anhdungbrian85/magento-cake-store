/**
 * @copyright  2023 247Commerce
 */

define([
	'jquery',
	'Amasty_StorePickupWithLocator/js/model/pickup/pickup-data-resolver',
    'locationContext',
    'uiRegistry',
    'Magento_Customer/js/customer-data',
	'mage/translate',
    'Magento_Checkout/js/action/set-shipping-information',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/select-billing-address',
    'Magento_Checkout/js/model/shipping-save-processor/payload-extender',
    'mage/url'
], function (
	$,
	pickupDataResolver,
    locationContext,
    registry,
    customerData,
    translate,
    setShippingInformationAction,
    quote,
    selectBillingAddressAction,
    payloadExtender,
    url
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

        onValueChange: function (value) {
            var datepickerDate,
                selectedDate;
            this._super();
            url.setBaseUrl(BASE_URL);
            let urlAjax = url.build('checkout/pickup/pickup');
            
            // This is direct access to the element because need to push date object(not string) to customer data
            datepickerDate = $('#' + this.uid).datepicker('getDate');
            selectedDate = datepickerDate && typeof datepickerDate.getFullYear == 'function'
                ? datepickerDate
                : value;
            
            let date = datepickerDate && typeof datepickerDate.getFullYear == 'function'
                ? datepickerDate
                : new Date();
            let dateStr = date.getFullYear() + '-' + ('0' + (date.getMonth()+1)).slice(-2) + '-' + ('0' + date.getDate()).slice(-2) + " 00:00:00";
            pickupDataResolver.dateData(selectedDate);
            // customerData.set('selectedPickDate', selectedDate);
            $.ajax({
                url: urlAjax,
                type: 'POST',
                data: {
                    quoteId: quote.getQuoteId(),
                    selectedDate: dateStr
                }
            }).done(function(response) {
                if (response.error) {
                    console.log(response);
                }
            });
            this.getSelectedDay(datepickerDate, value);
            this.source.trigger('amStorepickup.date.change', {
                date: value,
                store: this.selectedStore
            });
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
