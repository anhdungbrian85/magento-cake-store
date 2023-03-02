var config = {
    map: {
        "*": {
            "Amasty_CheckoutCore/js/model/checkout-data-resolver-mixin": 
                "X247Commerce_Checkout/js/model/checkout-data-resolver-mixin",
            'Amasty_StorePickupWithLocator/template/checkout/pickup/pickup-store.html': 
               'X247Commerce_Checkout/template/checkout/pickup/pickup-store.html'
        }
    },
    config: {
        mixins: {
            "Amasty_StorePickupWithLocator/js/view/pickup/pickup-store": {
                "X247Commerce_Checkout/js/view/pickup/pickup-store-mixin": true
            }
        }
    }
};