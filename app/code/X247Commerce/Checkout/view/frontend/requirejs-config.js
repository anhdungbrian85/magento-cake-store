var config = {
    map: {
        "*": {
            locationContext: 'X247Commerce_Checkout/js/model/location-context',
        }
    },
    config: {
        mixins: {

            // Hide delivery date block when use pickup store 
            "Amasty_CheckoutCore/js/model/one-step-layout": {
                "X247Commerce_Checkout/js/model/one-step-layout-mixin": true
            },

            // preselect shipping method
            'Magento_Checkout/js/model/checkout-data-resolver': {
                'Amasty_CheckoutCore/js/model/checkout-data-resolver-mixin': false,
                'X247Commerce_Checkout/js/model/checkout-data-resolver-mixin': true
            },

            // change context model value when select shipping method
            'Magento_Checkout/js/view/shipping': {
                'Amasty_CheckoutCore/js/view/shipping-mixin': false,
                'X247Commerce_Checkout/js/view/shipping-mixin': true
            },

            // ignore cache pickup date
            'Amasty_StorePickupWithLocator/js/view/pickup/pickup-date': {
                "X247Commerce_Checkout/js/view/pickup/pickup-date-mixin": true
            },
            
        }
    }
};