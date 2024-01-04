define([
    'jquery'
], function ($) {
    'use strict';

    return function (widget) {

        $.widget('mage.validation', widget, {
            listenFormValidateHandler: function (event, validation) {
                var firstActive = $(validation.errorList[0].element || []),
                    lastActive = $(validation.findLastActive() ||
                        validation.errorList.length && validation.errorList[0].element || []),
                    windowHeight = $(window).height(),
                    parent, successList;

                if (lastActive.is(':hidden')) {
                    setTimeout(function (){
                        $('html, body').animate({
                            scrollTop: lastActive.offset().top - windowHeight / 2
                        });
                        
                    }, 200)

                }

                // ARIA (removing aria attributes if success)
                successList = validation.successList;

                if (successList.length) {
                    $.each(successList, function () {
                        $(this)
                            .removeAttr('aria-describedby')
                            .removeAttr('aria-invalid');
                    });
                }

                // Removed scrollTo for popup
                if (firstActive.length) {
                    firstActive.trigger('focus');
                }
            }
        });

        return $.mage.validation;
    }
});
