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
    'Magento_Ui/js/modal/confirm',
    'Magento_Customer/js/customer-data',
    'jquery-ui-modules/widget'
], function ($, $t, _, idsResolver, productInfoResolver, urlBuilder, modal, confirmation, customerData) {
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
            if (window.preventAddToCartAction) {
                return;
            }
            var self = this,
                optionValues = [],
                deliveryType = window.localStorage.getItem('delivery_type') ?? 0,
                productId = window.indexSwatch ? '' : idsResolver(form)[0],
                alreadyInCart = false,
                lead_delivery = window.leadDelivery ? JSON.parse(window.leadDelivery) : [],
                index = window.indexSwatch ? JSON.parse(window.indexSwatch) : {},
                cart = customerData.get('cart')().items;

            $.each(form.serializeArray(), function (key, item) {
                if (item.name.indexOf('super_attribute') !== -1) {
                    optionValues.push(item.value);
                }
            });
            $.each(index, function (key, value) {
                var v = Object.values(value).sort();
                if (JSON.stringify(optionValues.sort()) == JSON.stringify(v)) {
                    productId = key;
                }
            });

            if (cart && cart.length > 0) {
                for (var i=0; i<cart.length; i++){
                    let item = cart[i];
                  if (item.product_type == "configurable") {
                      let op = [];
                      let option = item.options;

                      for (var j=0; j<option.length; j++){
                          op.push(option[j].option_value);
                      }

                      if (item.product_id == idsResolver(form)[0] && JSON.stringify(optionValues.sort()) == JSON.stringify(op.sort())) {
                          alreadyInCart = true;
                          break;
                      }
                  }
                  if (item.product_type == "simple") {
                      if (item.product_id == productId) {
                          alreadyInCart = true;
                          break;
                      }
                  }
                }
            }
            if (lead_delivery[productId] != undefined && lead_delivery[productId] > 1 && deliveryType == 2 && alreadyInCart == false) {
                confirmation({
                    title: $.mage.__('Notice!'),
                    content: 'This product is not available for collection in 1 hour. Do you want to continue?',
                    actions: {
                        confirm: function() {
                            self.ajaxSubmit(form);
                        },
                        cancel: function() {
                            // do something when the cancel button is clicked
                        },
                        always: function() {
                            // do something when the modal is closed
                        }
                    },
                    buttons: [{
                        text: $.mage.__('Cancel'),
                        class: 'action-secondary action-dismiss',
                        click: function (event) {
                            this.closeModal(event);
                        }
                    }, {
                        text: $.mage.__('Add to basket'),
                        class: 'action-primary action-accept',
                        click: function (event) {
                            this.closeModal(event, true);
                        }
                    }]
                });
            } else {
                self.ajaxSubmit(form);
            }
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
                    localStorage.setItem("wp_messages_loaded", '0');
                    $(document).trigger('ajax:addToCart', {
                        'sku': form.data().productSku,
                        'productIds': productIds,
                        'productInfo': productInfo,
                        'form': form,
                        'response': res
                    });

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
                                var addmoreSidebar = $('#addmore-sidebar').html(res.output);
                                var tocartForms = addmoreSidebar.find('.popup-tocart.tocart-form')

                                tocartForms.on('submit', function(e){
                                    e.preventDefault();
                                    self.submitForm($(this));
                                })
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
                                    modal(options, $('#add-more-product'));
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
                                $(self.options.addToCartButtonSelector).prop('disabled', false);
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
