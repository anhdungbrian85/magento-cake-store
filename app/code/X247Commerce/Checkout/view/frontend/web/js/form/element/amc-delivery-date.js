/**
 * Delivery Date Calendar Element View
 */
define([
    'ko',
    'jquery',
    'underscore',
    'Magento_Ui/js/form/element/date',
    'mage/translate',
    'locationContext',
    'Amasty_StorePickupWithLocator/js/model/pickup/pickup-data-resolver'
], function (ko, $, _, AbstractField, $t, locationContext, pickupDataResolver) {
    'use strict';


    return AbstractField.extend({
        defaults: {
            amcheckout_days: [],
            amcheckout_firstDay: 0
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
            this.options.dayNameIndex = {
                'sunday': 0,
                'monday': 1,
                'tuesday': 2,
                'wednesday': 3,
                'thursday': 4,
                'friday': 5,
                'saturday': 6
            };
            this.options.nextText = $t('Next');
            this.options.prevText = $t('Prev');
            this.options.weekHeader = $t('Wk');
            this.options.minDate = new Date();
            this.options.showOn = 'both';
            this.options.firstDay = this.amcheckout_firstDay;

            var self = this;
            this.deliveryDateConfig = ko.pureComputed(function() {
            	return locationContext.deliveryDateConfig();
            });

            var store = pickupDataResolver.getStoreById(locationContext.storeLocationId());
            if (locationContext.storeLocationId()) {
                if (store && store.schedule_id) {
                    var schedule = pickupDataResolver.getScheduleByScheduleId(store.schedule_id),
                        allowedDays = [];
                    
                    _.each(schedule, function(weekDay, dayName) {
                        if (weekDay[dayName+'_'+'status'] == 1) {
                            allowedDays.push(self.options.dayNameIndex[dayName]);
                        }
                    })

                    if (allowedDays.length) {
                        allowedDays.sort()
                        locationContext.deliveryDateConfig(allowedDays)
                    }
                }
            }

            if (this.deliveryDateConfig().length > 0) {
                this.options.beforeShowDay = this.restrictDates.bind(this);
            }

            this.prepareDateTimeFormats();
            this.setDateToFirstPickupDate(store);

            return this;
        },

        /**
         * Restrict dates
         * @param {Date} d
         * @returns {[Boolean, String]}
         */
        restrictDates: function (d) {
            return [$.inArray(d.getDay(), this.deliveryDateConfig()) != -1, ''];
        },

        setDateToFirstPickupDate: function (store) {
            var firstPickupDate = this.getFirstPickupDate(store), 
                self = this;

            this.firstPickupDate = firstPickupDate;

            // This is direct access to the element because change of value does not trigger change of datepicker input
            var setDatePicker = setInterval(function() {
                if ($('#' + self.uid).length) {
                    $('#' + self.uid).datepicker('setDate', firstPickupDate);
                    clearInterval(setDatePicker);
                }
                
            }, 500);            
        },

        getFirstPickupDate: function (store) {
            var now = new Date(), // today
                index;
            
            if (!store.schedule_id) {
                return now;
            }

            if (now.getHours() >= 16) {
                var minPickupDate = this.getNextOpenDay(now)
                return minPickupDate;
            }
            // break loop after the 31st iteration
            
            return now;
        },

        getNextOpenDay: function(date) {
            date = new Date(+date);
            do {
                date.setDate(date.getDate() + 1);
            }   while (
                $.inArray(date.getDay(), this.deliveryDateConfig())  == -1
                //@todo check not in Holidays
            )
            return date;
        },

        getHolidays: function(store) 
        {
            return [];
        }


    });
});
