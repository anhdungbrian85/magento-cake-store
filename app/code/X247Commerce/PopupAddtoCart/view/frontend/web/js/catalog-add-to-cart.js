/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/translate',
    'underscore',
    'Magento_Catalog/js/product/view/product-ids-resolver',
    'Magento_Catalog/js/product/view/product-info-resolver',
    'mage/url',
    'Magento_Ui/js/modal/modal',
    'Magento_Customer/js/customer-data',
    'magnificPopup',
    'weltpixel_quickview',
    'jquery-ui-modules/widget'
], function ($, $t, _, idsResolver, productInfoResolver, urlBuilder, modal, customerData, magnificPopup, weltpixel_quickview) {
    'use strict';

    $.widget('mage.catalogAddToCart', {
        options: {
            processStart: null,
            processStop: null,
            bindSubmit: true,
            minicartSelector: '[data-block="minicart"]',
            messagesSelector: '[data-placeholder="messages"]',
            productStatusSelector: '.stock.available',
            addToCartButtonSelector: '.action.tocart',
            addToCartButtonDisabledClass: 'disabled',
            addToCartButtonTextWhileAdding: '',
            addToCartButtonTextAdded: '',
            addToCartButtonTextDefault: '',
            productInfoResolver: productInfoResolver
        },

        /** @inheritdoc */
        _create: function () {
            if (this.options.bindSubmit) {
                this._bindSubmit();
            }
            $(this.options.addToCartButtonSelector).prop('disabled', false);
        },

        /**
         * @private
         */
        _bindSubmit: function () {
            var self = this;

            if (this.element.data('catalog-addtocart-initialized')) {
                return;
            }

            this.element.data('catalog-addtocart-initialized', 1);
            this.element.on('submit', function (e) {
                e.preventDefault();
                self.submitForm($(this));
            });
        },

        /**
         * @private
         */
        _redirect: function (url) {
            var urlParts, locationParts, forceReload;

            urlParts = url.split('#');
            locationParts = window.location.href.split('#');
            forceReload = urlParts[0] === locationParts[0];

            window.location.assign(url);

            if (forceReload) {
                window.location.reload();
            }
        },

        /**
         * @return {Boolean}
         */
        isLoaderEnabled: function () {
            return this.options.processStart && this.options.processStop;
        },

        /**
         * Handler for the form 'submit' event
         *
         * @param {jQuery} form
         */
        submitForm: function (form) {
            this.ajaxSubmit(form);
        },

        /**
         * @param {jQuery} form
         */
        ajaxSubmit: function (form) {
            var self = this,
                productIds = idsResolver(form),
                productInfo = self.options.productInfoResolver(form),
                formData;

            $(self.options.minicartSelector).trigger('contentLoading');
            self.disableAddToCartButton(form);
            formData = new FormData(form[0]);

            console.log(form);

            $.ajax({
                url: form.prop('action'),
                data: formData,
                type: 'post',
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,

                /** @inheritdoc */
                beforeSend: function () {
                    if (self.isLoaderEnabled()) {
                        $('body').trigger(self.options.processStart);
                    }
                },

                /** @inheritdoc */
                success: function (res) {
                    var eventData, parameters;

                    $(document).trigger('ajax:addToCart', {
                        'sku': form.data().productSku,
                        'productIds': productIds,
                        'productInfo': productInfo,
                        'form': form,
                        'response': res
                    });

                    /**
                     * Open popup confirmation
                     */
                    let wpConfirmationPopupOptions = customerData.get('wp_confirmation_popup')();
                    if (wpConfirmationPopupOptions.confirmation_popup_content) {
                        $.magnificPopup.open({
                            items: {
                                src: wpConfirmationPopupOptions.confirmation_popup_content,
                                type: 'inline'
                            },
                            callbacks: {
                                beforeClose: function() {
                                    $('[data-block="minicart"]').trigger('contentLoading');
                                }
                            },
                            mainClass: 'mfp-wp-confirmation-popup'
                        });

                    }
                    /**
                     * Open popup confirmation
                     */

                    if (self.isLoaderEnabled()) {
                        $('body').trigger(self.options.processStop);
                    }

                    if (res.backUrl) {
                        eventData = {
                            'form': form,
                            'redirectParameters': []
                        };
                        // trigger global event, so other modules will be able add parameters to redirect url
                        $('body').trigger('catalogCategoryAddToCartRedirect', eventData);

                        if (eventData.redirectParameters.length > 0 &&
                            window.location.href.split(/[?#]/)[0] === res.backUrl
                        ) {
                            parameters = res.backUrl.split('#');
                            parameters.push(eventData.redirectParameters.join('&'));
                            res.backUrl = parameters.join('#');
                        }

                        self._redirect(res.backUrl);

                        return;
                    }

                    if (res.messages) {
                        $(self.options.messagesSelector).html(res.messages);
                    }

                    if (res.minicart) {
                        $(self.options.minicartSelector).replaceWith(res.minicart);
                        $(self.options.minicartSelector).trigger('contentUpdated');
                    }

                    if (res.product && res.product.statusText) {
                        $(self.options.productStatusSelector)
                            .removeClass('available')
                            .addClass('unavailable')
                            .find('span')
                            .html(res.product.statusText);
                    }
                    self.enableAddToCartButton(form);


                    if ( ! form.hasClass('popup-tocart') ) {
                        $.ajax({
                            url: urlBuilder.build("quickview/addtocart/popup"),
                            data: formData,
                            type: 'post',
                            dataType: 'json',
                            cache: false,
                            contentType: false,
                            processData: false,

                            /** @inheritdoc */
                            success: function (res) {
                                console.log(res);
                                $('#addmore-sidebar').html(res.output);

                                    $('#addmore-sidebar .product-tab:first-child()').addClass('active');
                                    $('#addmore-sidebar .product-tab:first-child() .tab-content').slideDown();
                                    var options = {
                                        type: 'popup',
                                        responsive: true,
                                        title: $t('More To Add'),
                                        modalClass: 'popup-more-to-add',
                                        buttons: [{
                                            text: $t('Ok'),
                                            class: '',
                                            click: function () {
                                                this.closeModal();
                                            }
                                        }]
                                    };
                                    var popup = modal(options, $('#add-more-product'));
                                    $('#add-more-product').modal('openModal');

                                    $('.tab-title').on('click', function(e) {
                                        if ( $(this).parent().hasClass('active') ) {
                                            $(this).parent().removeClass('active');
                                            $(this).siblings('.tab-content').slideUp();
                                        } else {
                                            $(this).parent().addClass('active');
                                            $(this).siblings('.tab-content').slideDown();
                                        }
                                    });

                            },

                            /** @inheritdoc */
                            error: function (res) {
                                console.log(res);
                            },

                            /** @inheritdoc */
                            complete: function (res) {
                                if (res.state() === 'rejected') {
                                    location.reload();
                                }
                            }
                        });
                    } else {
                        self.enableAddToCartButtonPopup(form);
                    }


                },

                /** @inheritdoc */
                error: function (res) {
                    $(document).trigger('ajax:addToCart:error', {
                        'sku': form.data().productSku,
                        'productIds': productIds,
                        'productInfo': productInfo,
                        'form': form,
                        'response': res
                    });
                },

                /** @inheritdoc */
                complete: function (res) {
                    if (res.state() === 'rejected') {
                        location.reload();
                    }
                }
            });
        },

        /**
         * @param {String} form
         */
        disableAddToCartButton: function (form) {
            var addToCartButtonTextWhileAdding = this.options.addToCartButtonTextWhileAdding || $t('Adding...'),
                addToCartButton = $(form).find(this.options.addToCartButtonSelector);

            addToCartButton.addClass(this.options.addToCartButtonDisabledClass);
            addToCartButton.find('span').text(addToCartButtonTextWhileAdding);
            addToCartButton.prop('title', addToCartButtonTextWhileAdding);
        },

        /**
         * @param {String} form
         */
        enableAddToCartButton: function (form) {
            var addToCartButtonTextAdded = this.options.addToCartButtonTextAdded || $t('Added'),
                self = this,
                addToCartButton = $(form).find(this.options.addToCartButtonSelector);

            addToCartButton.find('span').text(addToCartButtonTextAdded);
            addToCartButton.prop('title', addToCartButtonTextAdded);

            setTimeout(function () {
                var addToCartButtonTextDefault = self.options.addToCartButtonTextDefault || $t('Add to Cart');

                addToCartButton.removeClass(self.options.addToCartButtonDisabledClass);
                addToCartButton.find('span').text(addToCartButtonTextDefault);
                addToCartButton.prop('title', addToCartButtonTextDefault);
            }, 1000);
        },

        enableAddToCartButtonPopup: function (form) {
            var addToCartButtonTextAdded = this.options.addToCartButtonTextAdded || $t('Added'),
                self = this,
                addToCartButton = $(form).find(this.options.addToCartButtonSelector);

            addToCartButton.find('span').text(addToCartButtonTextAdded);
            addToCartButton.prop('title', addToCartButtonTextAdded);
            addToCartButton.addClass('added');

        },
    });

    return $.mage.catalogAddToCart;
});
