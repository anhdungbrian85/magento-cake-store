var config = {
    map: {
        "*": {
            locationContext: 'X247Commerce_Checkout/js/model/location-context',

            // bind afterRender function 
            'Amasty_StorePickupWithLocator/template/checkout/pickup/pickup-store.html': 
               'X247Commerce_Checkout/template/checkout/pickup/pickup-store.html'
        }

    },
    config: {
        mixins: {
            // preselect Store Location Pickup 
            "Amasty_StorePickupWithLocator/js/view/pickup/pickup-store": {
                "X247Commerce_Checkout/js/view/pickup/pickup-store-mixin": true
            },

            // Hide delivery date block when use pickup store 
            "Amasty_CheckoutCore/js/model/one-step-layout": {
                "X247Commerce_Checkout/js/model/one-step-layout-mixin": true
            },

            // preselect shipping method
            'Magento_Checkout/js/model/checkout-data-resolver': {
                'Amasty_CheckoutCore/js/model/checkout-data-resolver-mixin': false,
                'X247Commerce_Checkout/js/model/checkout-data-resolver-mixin': true
            },

            'Magento_Checkout/js/view/shipping': {
                'Amasty_CheckoutCore/js/view/shipping-mixin': false,
                'X247Commerce_Checkout/js/view/shipping-mixin': true
            },
        }
    }
};