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
            $(document).on('click','.filter-by .filter-label', function() {
                if (!$(this).hasClass('active')) {
                    $(this).addClass('active');
                }

                if($(this).css("margin-right") == "400px") {
                    $('.sidebar.sidebar-main').animate({"margin-right": '-=400'});
                } else {
                    $('.sidebar.sidebar-main').animate({"margin-right": '+=400'});
                }
            });
        },

        _hideFilter: function() {
            $(document).on('click','.close-desktop', function() {
                if($(".filter-label").hasClass('active')){
                    $('.sidebar.sidebar-main').animate({"margin-right": '-=400'});
                    $('.filter-label').removeClass('active');
                } else {
                    $('.sidebar.sidebar-main').animate({"margin-right": '-=400'});
                }
            });
        },
    });
    return $.cakebox.slideSortBy;
});
