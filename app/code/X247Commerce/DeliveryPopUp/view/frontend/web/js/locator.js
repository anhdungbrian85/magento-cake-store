define([
    'jquery',
    'mage/url',
    'Amasty_Storelocator/js/model/states-storage',
    'Magento_Customer/js/customer-data',
    'mage/translate',
    'Amasty_Storelocator/vendor/chosen/chosen.min',
    'Amasty_Storelocator/vendor/jquery.ui.touch-punch.min',
    'Magento_Ui/js/lib/knockout/bindings/range',
    'Magento_Ui/js/modal/modal',
    'jquery/jquery-ui',
    'jquery-ui-modules/slider',
], function ($, url, statesStorage, customerData) {
    $.widget('mage.amLocator', {
        options: {},
        url: null,
        useBrowserLocation: null,
        useGeo: null,
        imageLocations: null,
        map: {},
        marker: {},
        storeListIdentifier: '',
        mapId: '',
        mapContainerId: '',
        needGoTo: false,
        markerCluster: {},
        bounds: {},
        selectors: {
            filterContainer: '[data-amlocator-js="filters-container"]',
            attributeForm: '[data-amlocator-js="attributes-form"]',
            multipleSelect: '[data-amlocator-js="multiple-select"]',
            radiusSelectValue: '[data-amlocator-js="radius-value"]',
            resetSelector: '[data-amlocator-js="reset"]',
            addressSelector: '[data-amlocator-js="address"]',
            searchSelector: '[data-amlocator-js="search"]',
            attributeFilterTitle: '[data-amlocator-js="filters-title"]'
        },
        hiddenState: '-hidden',
        latitude: 0,
        longitude: 0,
        postcode: '',

        _create: function () {
            this.ajaxCallUrl = this.options.ajaxCallUrl;
            this.ajaxSelectUrl = this.options.ajaxSelectUrl;
            this.useGeo = this.options.useGeo;
            this.imageLocations = this.options.imageLocations;
            this.mapContainer = $('#map-container-delivery-popup');

            this.initializeMap();
            this.initializeFilter();
            this.Amastyload();
            this.bindSelectLocation();
        },

        bindSelectLocation: function() {
            let self = this;
            url.setBaseUrl(BASE_URL);
            var redirectUrl = url.build('celebration-cakes/click-collect-1-hour.html');

            $(document).on('click', '.select-location', function() {
                let isProductPage = false;
                let addToCartFormData = '';
                if ($('#product_addtocart_form').length) {
                    isProductPage = true;
                    var formBase = document.getElementById('product_addtocart_form');
                    addToCartFormData = new FormData(formBase);
                }
                const location_id = $(this).data('location-id');
                const delivery_type = $('[name="delivery-type"]:checked').val();
                addToCartFormData.append('location_id', location_id);
                addToCartFormData.append('delivery_type', delivery_type);
                addToCartFormData.append('is_product_page', isProductPage);

                setTimeout(() => {
                    window.scroll({ top: -1, left: 0, behavior: "smooth" });
                }, 1);
                $.ajax({
                    url: self.ajaxSelectUrl,
                    type: 'POST',
                    data: addToCartFormData,
                    showLoader: true,
                    processData: false,
                    contentType: false

                }).done($.proxy(function (response) {
                    const sections = ['cart'];
                    customerData.invalidate(sections);
                    customerData.reload(sections, true);
                    window.localStorage.setItem('delivery_type', delivery_type);
                    console.log('response', response);
                    if (response.confirmation_popup_content) {
                        let confirmation_popup_content = response.confirmation_popup_content;
                        let parentBody = window.parent.document.body;
                        $("#custom-delivery-popup-modal").modal("closeModal");
                        $('<div />').html(confirmation_popup_content)
                            .modal({
                                autoOpen: true,
                                modalClass: 'wp-confirmation-popup-wrapper',
                                modalCloseBtn: '.mfp-close',
                                closed: function (e) {
                                    console.log('closed');
                                    if (delivery_type != 2) {
                                        window.location.reload();
                                    } else {
                                        window.location.href = redirectUrl;
                                    }
                                },
                                buttons: [{
                                    text: "Continue Shopping",
                                    attr: {
                                        'data-action': 'confirm'
                                    },
                                    'class': 'action primary',
                                    click: function () {
                                        this.closeModal();
                                        $('.mfp-close', parentBody).trigger('click');
                                    }
                                },
                                    {
                                        text: "Go To Checkout",
                                        attr: {
                                            'data-action': 'cancel'
                                        },
                                        'class': 'action primary',
                                        click: function () {
                                            parent.window.location = window.location.origin + '/checkout'
                                        }
                                    }],

                                callbacks: {
                                    beforeClose: function() {
                                        console.log('beforeClose');
                                        $('[data-block="minicart"]').trigger('contentLoading');
                                    }
                                },
                            });
                    } else {
                        if (delivery_type != 2) {
                            window.location.reload();
                        } else {
                            window.location.href = redirectUrl;
                        }
                    }

                }));
            })
        },

        navigateMe: function () {
            var self = this;

            self.needGoTo = 1;

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function (position) {

                    if (!self.mapContainer.find('.amlocator-text').val()) {
                        self.latitude = position.coords.latitude;
                        self.longitude = position.coords.longitude;
                    }

                    self.makeAjaxCall(1);
                }, this.navigateFail.bind(self));
            } else {
                alert($.mage.__('Sorry we\'re unable to display the nearby stores because the "Use browser location" option is disabled in the module settings. Please, contact the administrator.'));
            }
        },

        navigateFail: function (error) {
            // error param exists when user block browser location
            if (this.options.useGeoConfig == 1) {
                this.makeAjaxCall(1);
            } else if (error.code == 1) {
                alert(error.message);
            }
        },

        collectParams: function (sortByDistance, isReset) {
            var self = this;
            return {
                'lat': this.latitude,
                'lng': this.longitude,
                'radius': this.getRadius(isReset),
                'product': this.options.productId,
                'category': this.options.categoryId,
                'attributes': this.mapContainer.find(this.selectors.attributeForm).serializeArray(),
                'sortByDistance': sortByDistance,
                'delivery-type': $('[name="delivery-type"]:checked').val(),
                'dest': self.postcode
            };
        },

        getMeasurement: function () {
            if (this.mapContainer.find('#amlocator-measurement').length > 0) {
                return this.mapContainer.find('#amlocator-measurement').val();
            }

            return 'km';
        },

        getRadius: function (isReset) {
            var radius = null;
            var currentRadiusValue = this.mapContainer.find(this.selectors.radiusSelectValue);

            if (isReset) {
                return 0;
            }

            radius = currentRadiusValue.val();

            if (this.getMeasurement() == 'km') {
                radius /= 1.609344;
            }

            return radius;
        },

        makeAjaxCall: function (sortByDistance, isReset) {
            var self = this,
                sortByDistance = sortByDistance || 1;
                params = this.collectParams(sortByDistance, isReset);
            var errorMessage = "<div class='results-no-delivery'><span>There is no delivery available for the requested postcode. Please try again with a different postcode or choose the Collect in Store option.</span></div>";

            if ($('.results-no-delivery').length) {
                $('.results-no-delivery').remove();
            }
            $.ajax({
                url: self.ajaxCallUrl,
                type: 'POST',
                data: params,
                showLoader: true
            }).done($.proxy(function (response) {

                if (response.store_location_id) {
                    if (response.redirect_url) {
                        window.location.href = response.redirect_url;
                    }   else {
                        window.location.reload();
                    }

                } else if (response.delivery_status == false) {
                    if ($('[name="delivery-type"]:checked').val() == 1) {
                        $('.delivery-popup.text').append(errorMessage);
                    }
                } else {
                    response = JSON.parse(response);
                    self.options.jsonLocations = response;
                    self.getIdentifiers();
                    self.Amastyload();
                }

            }));
        },

        calculateDistance: function (lat, lng) {
            measurement = this.getMeasurement();

            for (var location in this.options.jsonLocations.items) {
                var distance = MarkerClusterer.prototype.distanceBetweenPoints_(
                        new google.maps.LatLng(
                            lat,
                            lng
                        ),
                        new google.maps.LatLng(
                            this.options.jsonLocations.items[location].lat,
                            this.options.jsonLocations.items[location].lng
                        )
                    ),

                    measurementLabel = $.mage.__('km');

                if (measurement == 'mi') {
                    distance /= 1.609344;
                    measurementLabel = $.mage.__('mi');
                }

                var locationId = this.options.jsonLocations.items[location].id,
                    distanceText = parseInt(distance) + ' ' + measurementLabel;

                this.mapContainer.find('#amasty_distance_' + locationId).show().find('span.amasty_distance_number').text(distanceText);
            }
        },

        Amastyload: function () {
            this.deleteMarkers(this.options.mapId);
            var self = this,
                mapId = this.options.mapId;

            this.processLocation();
            this.initializeStoreList();

            if (this.options.enableClustering) {
                this.markerCluster = new MarkerClusterer(this.map[this.options.mapId], this.marker[this.options.mapId], { imagePath: this.imageLocations + '/m' });
            }

            this.geocoder = new google.maps.Geocoder();
        },

        initializeMap: function () {
            var myOptions = {
                    zoom: 9,
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                },

                self = this;

            self.infowindow = [];
            self.marker[self.options.mapId] = [];
            self.map[self.options.mapId] = [];
            self.map[self.options.mapId] = new google.maps.Map($('#' + self.options.mapId)[0], myOptions);

            if (self.options.showSearch) {
                var address = self.mapContainer.find('.amlocator-text')[0],
                    autocompleteOptions = {
                        componentRestrictions: { country: self.options.allowedCountries },
                        fields: [ 'geometry.location', 'address_components' ]
                    },
                    autocomplete = new google.maps.places.Autocomplete(address, autocompleteOptions);

                google.maps.event.addListener(autocomplete, 'place_changed', function () {
                    var place = autocomplete.getPlace();

                    if (place.geometry != null) {
                        self.latitude = place.geometry.location.lat();
                        self.longitude = place.geometry.location.lng();

                        if (place.address_components != null) {
                            for (var i = 0; i < place.address_components.length; i++) {
                                for (var j = 0; j < place.address_components[i].types.length; j++) {
                                    if (place.address_components[i].types[j] == 'postal_code') {
                                        self.postcode = place.address_components[i].long_name;
                                    }
                                }
                            }
                        }

                        if (self.options.enableSuggestionClickSearch) {
                            self.makeAjaxCall();
                            self.toggleMapButtons(true);
                        }

                        if (self.options.enableCountingDistance) {
                            self.calculateDistance(place.geometry.location.lat(), place.geometry.location.lng());
                        }
                    } else {
                        alert($.mage.__('You need to choose address from the dropdown with suggestions.'));
                    }
                });
            }

            if (self.options.automaticLocate) {
                self.navigateMe();
            }
        },

        initializeFilter: function () {
            var self = this;

            self.mapContainer.find('.amlocator-button.-nearby').click(function () {
                self.getIdentifiers($(this));
                self.ajaxCallUrl = self.options.ajaxCallUrl;
                self.navigateMe();
            });

            self.mapContainer.find('.amlocator-text').on('keyup', function () {
                if (event.keyCode === 13) {
                    event.preventDefault();
                    self.mapContainer.find('.amlocator-text').click();
                }
            });

            if (this.options.isRadiusSlider) {
                this.createRadiusSlider();
            }

            self.mapContainer.find(self.selectors.multipleSelect).chosen({
                placeholder_text_multiple: $.mage.__('Select Some Options')
            });

            self.mapContainer.find('.amlocator-clear').on('click', function (e) {
                e.preventDefault();
                var attrForm = $(this).parents(self.selectors.filterContainer)
                    .find(self.selectors.attributeForm);

                attrForm.find('option:selected').removeAttr('selected');
                attrForm[0].reset();
                attrForm.find(self.selectors.multipleSelect).val(null).trigger('chosen:updated');
                self.getIdentifiers($(this));
                self.makeAjaxCall();
            });

            self.mapContainer.find(this.selectors.searchSelector).on('click', self.searchLocations.bind(this));
            self.mapContainer.find(this.selectors.addressSelector).on('keydown', function (e) {

                if (e.keyCode !== 13) {
                    return;
                }

                // self.searchLocations();
            });

            self.mapContainer.find(this.selectors.attributeFilterTitle).on('click', function () {
                $(this).parent().find(self.selectors.filterContainer).slideToggle();
                $(this).find('.amlocator-arrow').toggleClass('-down');
            });

            self.mapContainer.find(this.selectors.attributeFilterTitle).on('keyup', function (e) {
                if (e.keyCode !== 13) {
                    return;
                }

                $(this).triggerHandler('click');
            });

            self.mapContainer.find('.amlocator-filter-attribute').on('click', function () {
                self.getIdentifiers($(this));
                $(this).parent().find(self.selectors.filterContainer).slideToggle();
                self.makeAjaxCall();
            });

            self.mapContainer.find(this.selectors.resetSelector).on('click', this.resetMap.bind(this));
            self.mapContainer.find('[name="delivery-type"]').on('change', function(){
                let value = self.mapContainer.find(self.selectors.addressSelector).val();
                if(value.length > 0){
                    self.mapContainer.find('.amlocator-wrapper .amlocator-stores-wrapper').html('');
                    self.makeAjaxCall();
                }
            })
        },

        toggleFilters: function () {

        },

        toggleMapButtons: function (isShow) {
            $(this.selectors.resetSelector).toggleClass(this.hiddenState, !isShow);
            $(this.selectors.searchSelector).toggleClass(this.hiddenState, isShow);
        },

        resetMap: function () {
            this.makeAjaxCall(false, true);
            $(this.selectors.addressSelector).val('');
            this.toggleMapButtons(false);
        },

        searchLocations: function () {
            var self = this;

            if (!$(self.selectors.addressSelector).val()) {
                return false;
            }

            self.getIdentifiers($(this));
            self.makeAjaxCall();
            self.toggleMapButtons(true);
        },

        initializeStoreList: function () {
            var self = this,
                mapId = this.options.mapId;

            self.mapContainer.find('.amlocator-today').click(function () {
                $(this).next('.amlocator-week').slideToggle();
                $(this).find('.amlocator-arrow').toggleClass('-down');
                event.stopPropagation();
            });

            self.mapContainer.find('.amlocator-pager-container .item a').click(function () {
                self.getIdentifiers($(this));
                self.ajaxCallUrl = this.href;
                self.makeAjaxCall(false, true);
                event.preventDefault();
            });

            self.mapContainer.find('.amlocator-store-desc').click(function () {
                var id = $(this).attr('data-amid');

                self.getIdentifiers($(this));

                self.gotoPoint(id);
            });

            if (self.options.enableCountingDistance
                && self.latitude
                && self.longitude
            ) {
                self.calculateDistance(self.latitude, self.longitude);
            }

            statesStorage.storeListIsLoaded(true);
        },

        getIdentifiers: function (event) {
            if (event && !this.mapContainer) {
                this.mapContainer = event.parents().closest('.amlocator-map-container');
            }

            this.storeListIdentifier = this.mapContainer.find('.amlocator-store-list');
            this.mapIdentifier = this.mapContainer.find('.amlocator-map');
        },

        processLocation: function () {
            var self = this,
                locations = self.options.jsonLocations,
                curtemplate = '';

            self.bounds = new google.maps.LatLngBounds();

            for (var i = 0; i < locations.totalRecords; i++) {
                curtemplate = locations.items[i].popup_html;

                this.createMarker(locations.items[i].lat, locations.items[i].lng, curtemplate, locations.items[i].id, locations.items[i].marker_url);
            }

            for (var locationId in this.marker[this.options.mapId]) {
                if (this.marker[this.options.mapId].hasOwnProperty(locationId)) {
                    this.bounds.extend(this.marker[this.options.mapId][locationId].getPosition());
                }
            }

            this.map[this.options.mapId].fitBounds(this.bounds);

            if (locations.totalRecords === 1 || self.needGoTo) {
                google.maps.event.addListenerOnce(this.map[this.options.mapId], 'bounds_changed', function () {
                    self.map[self.options.mapId].setZoom(self.options.mapZoom);
                });
            }

            if (locations.totalRecords === 0) {
                google.maps.event.addListenerOnce(this.map[this.options.mapId], 'bounds_changed', function () {
                    self.map[self.options.mapId].setCenter(
                        new google.maps.LatLng(
                            0,
                            0
                        )
                    );
                    self.map[self.options.mapId].setZoom(2);
                    alert($.mage.__('Sorry, no locations were found.'));
                });
            }

            if (self.storeListIdentifier) {
                self.storeListIdentifier.html(locations.block);

                if (locations.totalRecords > 0 && self.needGoTo) {
                    self.gotoPoint(locations.items[0].id);
                    self.needGoTo = false;
                }
            }
        },

        gotoPoint: function (myPoint) {
            var self = this,
                mapId = self.mapIdentifier.attr('id') || self.options.mapId;

            self.closeAllInfoWindows(mapId);

            self.mapContainer.find('.-active').removeClass('-active');

            // add class if click on marker
            self.mapContainer.find('[data-amid=' + myPoint + ']').addClass('-active');
            self.map[mapId].setCenter(
                new google.maps.LatLng(
                    self.marker[mapId][myPoint].position.lat(),
                    self.marker[mapId][myPoint].position.lng()
                )
            );
            self.map[mapId].setZoom(self.options.mapZoom);
            self.marker[mapId][myPoint].infowindow.open(
                self.map[mapId],
                self.marker[mapId][myPoint]
            );
        },

        createMarker: function (lat, lon, html, locationId, marker) {
            var self = this,
                newmarker = new google.maps.Marker({
                    position: new google.maps.LatLng(lat, lon),
                    map: this.map[this.options.mapId],
                    icon: marker || ''
                });

            newmarker.infowindow = new google.maps.InfoWindow({
                content: html
            });
            newmarker.locationId = locationId;
            google.maps.event.addListener(newmarker, 'click', function () {
                self.mapIdentifier = $('#' + self.element.context.id);
                self.gotoPoint(this.locationId);
            });

            // using locationId instead 0, 1, 2, i counter
            this.marker[this.options.mapId][locationId] = newmarker;
        },

        closeAllInfoWindows: function (mapId) {
            var spans = $('#' + mapId + ' span');

            for (var i = 0, l = spans.length; i < l; i++) {
                spans[i].className = spans[i].className.replace(/\active\b/, '');
            }

            if (typeof this.marker[mapId] !== 'undefined') {
                for (var marker in this.marker[mapId]) {
                    if (this.marker[mapId].hasOwnProperty(marker)) {
                        this.marker[mapId][marker].infowindow.close();
                    }
                }
            }
        },

        createRadiusSlider: function () {
            var self = this,
                radiusValue = self.mapContainer.find(self.selectors.radiusSelectValue),
                radiusMeasurment = self.mapContainer.find('[data-amlocator-js="radius-measurment"]'),
                measurmentSelect = self.mapContainer.find('[data-amlocator-js="measurment-select"]');

            if (self.options.minRadiusValue <= self.options.maxRadiusValue) {
                var slider = self.mapContainer.find(self.selectors.radiusSlider).slider({
                        range: 'min',
                        min: self.options.minRadiusValue,
                        max: self.options.maxRadiusValue,
                        create: function () {
                            radiusValue.text($(this).slider('value'));

                            if (self.options.measurementRadius != '') {
                                radiusMeasurment.text(self.options.measurementRadius);
                            } else {
                                radiusMeasurment.text(measurmentSelect.val());
                            }

                            $('#' + self.options.searchRadiusId).val($(this).slider('value'));
                        },
                        slide: function (event, ui) {
                            radiusValue.text(ui.value);
                            radiusValue.val(ui.value);
                            $('#' + self.options.searchRadiusId).val(ui.value);
                        }
                    }),

                    radiusValueBuffer = '',
                    radiusValueTimer = '';

                self.mapContainer.find('.amlocator-tooltip').on('keyup', function (e) {
                    if (e.which !== 8 && e.which !== 0 && (e.which < 48 || e.which > 57)) {
                        return;
                    }

                    if (radiusValueTimer) {
                        clearTimeout(radiusValueTimer);
                    }

                    radiusValueBuffer += e.originalEvent.key;
                    radiusValue.html(radiusValueBuffer);
                    radiusValueTimer = setTimeout(function () {
                        if (radiusValueBuffer < self.options.minRadiusValue) {
                            radiusValueBuffer = self.options.minRadiusValue;
                        } else if (radiusValueBuffer > self.options.maxRadiusValue) {
                            radiusValueBuffer = self.options.maxRadiusValue;
                        }

                        radiusValue.html(radiusValueBuffer);
                        slider.slider('value', radiusValueBuffer);
                        radiusValueBuffer = '';
                    }, 1000);
                });

                slider.on('click', function () {
                    self.mapContainer.find('.amlocator-tooltip').focus();
                });
            }

            measurmentSelect.on('change', function () {
                radiusMeasurment.text(this.value);
            });
        },

        deleteMarkers: function (mapId) {
            if (!_.isEmpty(this.markerCluster)) {
                this.markerCluster.clearMarkers();
            }

            for (var marker in this.marker[mapId]) {
                if (this.marker[mapId].hasOwnProperty(marker)) {
                    this.marker[mapId][marker].setMap(null);
                }
            }

            this.marker[mapId] = [];
        }

    });

    return $.mage.amLocator;
});
