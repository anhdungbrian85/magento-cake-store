define([
    'jquery',
    'domReady!'
], function($){
    'use strict';
    $.widget('x247.popupeventcustomer', {
        options : {
            yearPresent : $('#year').val(),
            n : 0
        },

        _create: function() {

            var $widget = this;

            $('#button-add').on('click', function(){
                $('#edit-container').addClass('active new');
                $('#edit-container .edit-container-header .edit').hide();
                $('#edit-container .edit-container-content .create-another-reminder').hide();
            });

            $('.button-edit').on('click', function(){
                var parent = $(this).parents('.tb-item');
                $('#edit-id-value').val($(this).attr('data-value'));
                $('#month').val(+parent.find('.tb-item-value .month-value').attr('data-value')).change();
                $('#year').val(parent.find('.tb-item-value .year-value').text());
                $('#name').val(parent.find('.tb-item-value .name-value').text());
                $('#occasion').val(parent.find('.tb-item-value .occasion-value').text());
                $('#edit-container').addClass('active edit');
                $('#edit-container .edit-container-header .new').hide();
                $('#edit-container .edit-container-content .create-another-reminder').show();
                $('#day').val(+parent.find('.tb-item-value .day-value').text()).change();
            });

            $('#edit-container #close').on('click', function() {
                $('#edit-id-value').val("");
                $('#year').val("").change();
                $('#name').val("");
                $('#month').val("").change();
                $('#occasion').val("");
                $('#day').val("1").change();
                $('#edit-container').removeClass('active edit new');
                $('#edit-container .edit-container-header h1').show();
            });

            $widget.getMonths();

            $('#month').change(function () {

                $widget.getDaysInMonth();
            });

            $('#year').change(function () {

                $widget.getDaysInMonth();
            });
        },
        getMonths: function() {

            var months = new Array(12);
            months[1] = "Jan";
            months[2] = "Feb";
            months[3] = "Mar";
            months[4] = "Apr";
            months[5] = "May";
            months[6] = "Jun";
            months[7] = "Jul";
            months[8] = "Aug";
            months[9] = "Sep";
            months[10] = "Oct";
            months[11] = "Nov";
            months[12] = "Dec";

            for (var key in months) {
                
                $('#month')
                .append($("<option></option>")
                .attr("value", key)
                .text(months[key]));
            }
        },

        getDaysInMonth: function() {
            var $widget = this;
            var oddMonth = ['Jan', 'Mar', 'May', 'Jul', 'Sep', 'Nov'],
                month = $('#month').find(":selected").text(),
                n = 0;
            if (month != "") {
                if (oddMonth.includes(month)) {
                    n = 31;
                } else {
                    if (month == "Feb") {
                        if ( $widget.options.yearPresent % 4 == 0) {
                            n = 29;
                        } else {
                            n = 28;
                        }
                    } else {
                        n = 30;
                    }
                }
            }
            if (n != 0 && $widget.options.n != n) {
                $('#day').empty();
                for (let i = 1; i < n+1; i++) {
                    $('#day')
                    .append($("<option></option>")
                    .attr("value", i)
                    .text(i));
                }
            }

            $widget.options.n = n;

        }
    });
    
    return $.x247.popupeventcustomer;
});