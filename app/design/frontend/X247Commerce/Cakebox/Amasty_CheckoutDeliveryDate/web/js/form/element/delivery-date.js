/**
 * Delivery Date Calendar Element View
 */
define([
    'ko',
    'underscore',
    'jquery',
    'Magento_Ui/js/form/element/date',
    'mage/translate',
    'locationContext',
    'uiRegistry'
], function (ko, _, $, AbstractField, $t, locationContext, registry) {
    'use strict';

    function isToday(date) {
        const today = new Date();
        return today.toDateString() === date.toDateString();

    }

    return AbstractField.extend({
        defaults: {
            amcheckout_days: [],
            amcheckout_firstDay: 0
        },
        storesData: window.checkoutConfig.amastyLocations.stores,
        deliverStoreId: ko.observable(window.checkoutConfig.storeLocationId),
        timeSlotsWeekdayConfig: window.checkoutConfig.deliveryDateTimeSlots.weekday,
        timeSlotsWeekendConfig: window.checkoutConfig.deliveryDateTimeSlots.weekend,
        initialize: function () {
            this._super();
            var self = this;
            self.deliverStore = ko.computed(function(){
                return _.find(self.storesData, function(store) {
                    return store.id == self.deliverStoreId()
                })
            }, this);

        },
        initConfig: function () {
            this._super();
            this.options.closeText = $t('Done');
            this.options.currentText = $t('Today');
            this.options.dayNames = [
                $t('Sunday'),
                $t('Monday'),
                $t('Tuesday'),
                $t('Wednesday'),
                $t('Thursday'),
                $t('Friday'),
                $t('Saturday')
            ];
            this.options.dayNamesMin = [
                $t('Su'),
                $t('Mo'),
                $t('Tu'),
                $t('We'),
                $t('Th'),
                $t('Fr'),
                $t('Sa')
            ];
            this.options.dayNamesShort = [
                $t('Sun'),
                $t('Mon'),
                $t('Tue'),
                $t('Wed'),
                $t('Thu'),
                $t('Fri'),
                $t('Sat')
            ];
            this.options.monthNames = [
                $t('January'),
                $t('February'),
                $t('March'),
                $t('April'),
                $t('May'),
                $t('June'),
                $t('July'),
                $t('August'),
                $t('September'),
                $t('October'),
                $t('November'),
                $t('December')
            ];
            this.options.monthNamesShort = [
                $t('Jan'),
                $t('Feb'),
                $t('Mar'),
                $t('Apr'),
                $t('May'),
                $t('Jun'),
                $t('Jul'),
                $t('Aug'),
                $t('Sep'),
                $t('Oct'),
                $t('Nov'),
                $t('Dec')
            ];
            this.options.nextText = $t('Next');
            this.options.prevText = $t('Prev');
            this.options.weekHeader = $t('Wk');
            this.options.minDate = new Date();
            this.options.showOn = 'both';
            this.options.firstDay = this.amcheckout_firstDay;

            if (this.amcheckout_days.length > 0) {
                this.options.beforeShowDay = this.restrictDates.bind(this);
            }

            this.options.dateFormat = 'dd/mm/yy';

            this.prepareDateTimeFormats();

            return this;
        },

        /**
         * Restrict dates
         * @param {Date} d
         * @returns {[Boolean, String]}
         */
        restrictDates: function (d) {
            var deliverStoreData = this.deliverStore(),
                leadDelivery = locationContext.leadDeliveryTime(),
                today = new Date(),
                dayEnabled = true,
                storeCutoffTime = 16; // Hard fix cutoff time at 16:00

            //@todo: Cutoff time by store

            if (isToday(d)) {
                var timeToDeliver = deliverStoreData.current_timezone_time + (parseInt(locationContext.leadDeliveryTime()) * 3600),
                cutOffTime = new Date(today.getFullYear(), today.getUTCMonth(), today.getUTCDate(), storeCutoffTime),
                cutOffTimeToInt = Date.parse(cutOffTime)/1000 - (today.getTimezoneOffset() * 60);
                dayEnabled = timeToDeliver < cutOffTimeToInt;
            }

            if ($.inArray(d.getDay(), this.amcheckout_days) == -1) {
                dayEnabled = false;
            }
            return [
                dayEnabled, ''
            ];

        },

        onValueChange: function() {
            if (this.value()) {
                var TimeComponent = registry.get('checkout.steps.shipping-step.amcheckout-delivery-date.time'),
                    dateSelected = this.getDateFromValue(this.value()),
                    isWeekend = (dateSelected.getDay() === 0 || dateSelected.getDay() === 6),
                    TimeOptions= isWeekend ?
                        this.createTimeOption(this.timeSlotsWeekendConfig) :
                        this.createTimeOption(this.timeSlotsWeekdayConfig);

                //@TODO: holiday hours
                TimeComponent.options.pop();
                TimeComponent.options.push(TimeOptions);
            }
        },

        createTimeOption: function (timeConfig) {
            var Times = timeConfig.split('-'),
                TimeFrameStart = Times[0],
                TimeFrameEnd = Times[1];
            return {
                label: TimeFrameStart+':00 - '+TimeFrameEnd+ ':00',
                labeltitle: TimeFrameStart+':00 - '+TimeFrameEnd+ ':00',
                value: TimeFrameStart
            };
        },

        /**
         *
         * @param dateString
         * @returns {Date}
         */
        getDateFromValue: function (dateString) {
            var DateComponent = dateString.split('/');
            DateComponent.reverse();
            return new Date(DateComponent.join('/'));
        }

    });
});
