/**
 * Main Pickup Store UIElement
 */
define([
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/select-shipping-method',
    'Magento_Checkout/js/checkout-data',
    'Magento_Ui/js/form/element/select',
    'Magento_Customer/js/customer-data',
    'Amasty_StorePickupWithLocator/js/model/pickup/pickup-data-resolver',
    'Amasty_StorePickupWithLocator/js/model/pickup',
    'mage/translate'
], function (
    $,
    quote,
    selectShippingMethodAction,
    checkoutData,
    Select,
    customerData,
    pickupDataResolver,
    pickup
) {
    'use strict';

    return Select.extend({
        defaults: {
            value: '',
            datePickup: '',
            timePickup: '',
            chooseStore: false,
            store: '',
            caption: $.mage.__('Choose a store...'),
            storesSectionName: 'amasty-storepickup-data',
            selectedStoreSectionName: 'amasty-selected-pickup-info',
            template: 'X247Commerce_Checkout/pickup/pickup-store'
        },

        initConfig: function () {
            console.log('amasty-selected-pickup-info', JSON.parse(window.localStorage.getItem('mage-cache-storage'))['amasty-selected-pickup-info']);
            var stores,
                pickupData,
                amPickupConfig;

            this._super();
            pickupData = pickupDataResolver.pickupData;
            stores = pickupData().stores;
            amPickupConfig = window.checkoutConfig.amastyStorePickupConfig;

            if (stores
                && (pickupData().website_id !== amPickupConfig.websiteId
                    || pickupData().store_id !== amPickupConfig.storeId)
            ) {
                customerData.reload([ this.storesSectionName ], false);
            }
            
            this.options = stores || [];
            this.value = pickupDataResolver.getDataByKey('am_pickup_store');
            
            this.visible = pickup.isPickup();
            
            return this;
        },

        initObservable: function () {
            this.observe('options datePickup timePickup chooseStore store');

            pickupDataResolver.pickupData.subscribe(function (data) {
                this.options(data.stores);
            }, this);

            pickup.isPickup.subscribe(this.pickupStateObserver, this);
            pickupDataResolver.storeId.subscribe(this.storeObserver, this);

            this._super();

            return this;
        },

        initialize: function () {
            this._super();
            if (pickupDataResolver.storeId()) {
                pickupDataResolver.storeId.valueHasMutated();
            } 
            if (window.storeLocationData.store_location_id_selected) {
                selectShippingMethodAction('amstorepickup');
                checkoutData.setSelectedShippingRate('amstorepickup_amstorepickup');
                this.onChangeStore(window.storeLocationData.store_location_id_selected);
            }
            
            return this;
        },

        /**
         * @param {Number} storeId
         * @returns {void}
         */
        storeObserver: function (storeId) {
            if (storeId && +this.value() !== +storeId) {
                this.value(String(storeId));
            }
        },

        onChangeStore: function (storeId) {
            if (storeId) {
                pickupDataResolver.storeId(storeId);
            }
        },

        /**
         * @param {Boolean} isActive
         * @returns {void}
         */
        pickupStateObserver: function (isActive) {
            if (isActive) {
                pickupDataResolver.storeId.valueHasMutated();
            }
        },

        openMap: function () {
            this.source.trigger('amStorepickup.data.openMap');
        },

        getStoreLocationSelectedData: function () {
            let pickupData = pickupDataResolver.pickupData;
            if (!this.chooseStore()) {
                if (pickupDataResolver.storeId()) {
                    for (let i = 0; i < pickupData().stores.length; i++) {
                        if (pickupData().stores[i].id == window.storeLocationData.store_location_id_selected) {
                            return pickupData().stores[i];
                        }
                    }
                } 
             
                if (window.storeLocationData.store_location_id_selected == 0 || !pickupDataResolver.storeId()) {
                   return {name: ''};
                } 
            } else {
                return this.store();
            }
        },

        getSchedulePickUpData: function () {
            let pickupData = pickupDataResolver.pickupData;
            let selectedDate = new Date();
            let options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            if (pickupDataResolver.dateData()) {
                selectedDate = new Date(pickupDataResolver.dateData());
            } 
            return selectedDate.toLocaleDateString("en-US", options);
        },
        moveMapContainerDelivery: function () {
            $("#map-container-delivery-popup").detach().appendTo('#pickup-store-wrapper')
        },
    });
});
