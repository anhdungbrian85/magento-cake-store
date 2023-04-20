var config = {
    config: {
        mixins: {
            'Magento_ConfigurableProduct/js/configurable': {
                'X247Commerce_Products/js/model/skuswitch': true
            },
            'Magento_Swatches/js/swatch-renderer': {
                'X247Commerce_Products/js/model/swatch-skuswitch': true
            }
        }
    }
};