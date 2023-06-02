define([
    'jquery',
    'domReady!'
], function($){
    "use strict";
    $.widget('cakebox.slideSortBy', {
        _create: function () {
            this._showFilter();
            this._hideFilter();
        },

        _showFilter: function() {
            $('.filter-by .filter-label').unbind('click');
            $('.filter-by .filter-label').bind('click', function() {
                if (!$(this).hasClass('active')) {
                    $(this).addClass('active');
                }

                if($(this).css("margin-right") == "400px") {
                    $('.sidebar.sidebar-main').animate({"margin-right": '-400'});
                } else {
                    $('.sidebar.sidebar-main').animate({"margin-right": '0'});
                }
            });
        },

        _hideFilter: function() {
            $('.close-desktop').unbind('click');
            $('.close-desktop').bind('click',function() {
                    $('.sidebar.sidebar-main').animate({"margin-right": '-400'});
                    $('.filter-label').removeClass('active');
        
            });
        },
    });
    return $.cakebox.slideSortBy;
});
