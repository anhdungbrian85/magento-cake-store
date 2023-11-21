/**
 * Main Pickup Time UIElement
 */
define([
    'ko',
    'jquery',
    'Magento_Ui/js/form/element/select',
    'Magento_Customer/js/customer-data',
    'Amasty_StorePickupWithLocator/js/model/pickup',
    'Amasty_StorePickupWithLocator/js/model/pickup/pickup-data-resolver',
    'locationContext',
    'Amasty_StorePickupWithLocator/js/view/pickup/pickup-date'
], function (ko, $, Component, customerData, pickup, pickupDataResolver, locationContext) {
    'use strict';

    function createTimeInterval(unixTime, offset)
    {
        let dateUnix = new Date();
        dateUnix.setTime(unixTime * 1000 + offset * 60000);
        let label = dateUnix.toLocaleTimeString('en-US', {hour: 'numeric',minute: 'numeric',hour12: true, timeZone: 'Europe/London'});
        let UnixTimeTo = unixTime;

        return {
            fromInUnix: unixTime,
            label: label,
            labeltitle: label,
            toInUnix: UnixTimeTo,
            value: unixTime +'|'+ UnixTimeTo
        }
    }

    return Component.extend({
        holidays: window.checkoutConfig.store_location_holiday,
        defaults: {
            options: [],
            imports: {
                cartProductsDelay: '${$.parentName}.am_pickup_date:cartProductsDelay',
                selectedDayByName: '${$.parentName}.am_pickup_date:selectedDayByName',
                sameDayCutoffTime: '${$.parentName}.am_pickup_date:sameDayCutoffTime',
                storeScheduleSelected: '${$.parentName}.am_pickup_date:storeScheduleSelected',
                isTodaySelected: '${$.parentName}.am_pickup_date:isTodaySelected'
            },
            listens: {
                '${$.provider}:amStorepickup.date.change': 'setTimeIntervals'
            }
        },

        visibleComputed: ko.pureComputed(function () {
            return Boolean(pickupDataResolver.storeId() && pickupDataResolver.dateData() && pickup.isPickup());
        }),

        initialize: function () {
            var dateData;

            this._super();

            dateData = pickupDataResolver.dateData();

            if (dateData) {
                this.setTimeIntervals({
                    date: dateData,
                    store: pickupDataResolver.getCurrentStoreData()
                });
            }

            return this;
        },

        initConfig: function () {
            this._super();

            this.visible = this.visibleComputed();
            this.getPickupTimeFromCache();

            return this;
        },

        initObservable: function () {
            this._super();

            pickup.isPickup.subscribe(this.pickupStateObserver, this);
            this.visibleComputed.subscribe(this.visible);

            return this;
        },


        /**
         * Set time intervals by store schedule
         *
         * @param {Object} data
         * @return {void}
         */
        setTimeIntervals: function (data) {
            var selectedStore = data.store,
                timeIntervals,
                oldValue = this.value(),
                isOldTimeValid,
                isCachedTimeValid,
                selectedDate, holidays;

            if (data.date && selectedStore) {
                timeIntervals = pickupDataResolver.getTimeIntervalsByScheduleId(selectedStore.schedule_id);
                selectedDate = data.date.split("/").reverse().join("-");
                holidays = this.holidays.filter(function (item) {
                                return item.location_id == locationContext.storeLocationId()
                                    && selectedDate == item.date;
                            });
                if (!$.isEmptyObject(holidays)) {
                    holidays = holidays[0];
                }

                if (this.storeScheduleSelected || data.store.schedule_id) {
                    timeIntervals = timeIntervals[this.selectedDayByName];
                }

                if (timeIntervals) {
                    this.options((this.isTodaySelected || !$.isEmptyObject(holidays))
                        ? this.restrictTimeIntervals(timeIntervals, holidays)
                        : timeIntervals);
                }

                isOldTimeValid = this.options().some(function (interval) {
                    return interval.value === oldValue;
                });

                if (isOldTimeValid) {
                    this.value(oldValue);
                }

                if (this.getDataFromCache) {
                    isCachedTimeValid = this.options().some(function (interval) {
                        return interval.value === this.timeFromCache;
                    }.bind(this));
                }

                if (this.getDataFromCache && isCachedTimeValid) {
                    this.value(this.timeFromCache);
                }

                this.getDataFromCache = false;
            }
            if (this.options().length) {
                this.value(this.options()[0].value)
            }
            this.disabled(!data.date);
        },

        /**
         * Restrict time intervals by store schedule
         *
         * @param {Array} intervals
         * @returns {*}
         */
        restrictTimeIntervals: function (intervals, holidayTime) {
            var currentStore = pickupDataResolver.getCurrentStoreData() || {},
                currentStoreTime = currentStore.current_timezone_time,
                firstScheduledTimeUnix, endScheduledTimeUnix,
                filteredIntervals, holidayOpen, holidayClose;

            if (intervals.length) {
                holidayOpen = holidayTime.openInUnix - currentStore.current_timezone_offset * 60;
                holidayClose = holidayTime.closeInUnix - currentStore.current_timezone_offset * 60;

                if (intervals[0].fromInUnix > holidayOpen) {
                    let firstScheduledTimeUnix = intervals[0].fromInUnix;
                    let beforeOpen = [];
                    for (let i = holidayOpen; i < firstScheduledTimeUnix ; i+=1800) {
                        let today = new Date();
                        let pickupTimeSlot = createTimeInterval(i, currentStore.current_timezone_offset);

                        beforeOpen.unshift(pickupTimeSlot);
                    }
                    beforeOpen = beforeOpen.reverse();
                    intervals = beforeOpen.concat(intervals);   

                }
                if (intervals[intervals.length - 1].toInUnix < holidayClose) {
                    let lastScheduledTimeUnix = intervals[intervals.length - 1].toInUnix;

                    for (let i = lastScheduledTimeUnix; i < holidayClose ; i+=1800) {
                        let today = new Date();
                        let pickupTimeSlot = createTimeInterval(i, currentStore.current_timezone_offset);
                        intervals.push(pickupTimeSlot);
                    }
                }
            }
            firstScheduledTimeUnix = intervals[0].fromInUnix;

            filteredIntervals = intervals.filter(function (item) {

                if (!$.isEmptyObject(holidayTime)) {
                    let compare = (holidayTime.openInUnix < item.fromInUnix && holidayTime.closeInUnix > item.fromInUnix);
                    if (this.isTodaySelected) {
                        return compare && (item.fromInUnix >= (firstScheduledTimeUnix + parseInt(locationContext.leadDeliveryTime())*3600));
                    } else {
                        return compare;
                    }
                }
                if (this.isTodaySelected) {
                    return item.fromInUnix >= (firstScheduledTimeUnix + parseInt(locationContext.leadDeliveryTime())*3600);
                    // && item.toInUnix <= this.sameDayCutoffTime;
                }
            }.bind(this));

            return filteredIntervals;
        },

        onUpdate: function (pickupTime) {
            var pickupTimeOption = this.options().filter(function (elem) {
                return elem.value === pickupTime;
            })[0];

            pickupDataResolver.timeData(pickupTime);

            this.pickupTimeLabel = pickupTimeOption.label;
        },

        pickupStateObserver: function (isActive) {
            if (isActive) {
                this.getPickupTimeFromCache();
            }
        },

        getPickupTimeFromCache: function () {
            this.timeFromCache = pickupDataResolver.getDataByKey('am_pickup_time');
            this.getDataFromCache = !!this.timeFromCache;
        }

    });
});
