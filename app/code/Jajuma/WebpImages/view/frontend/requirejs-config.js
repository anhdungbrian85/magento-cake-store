var config = {
    deps: ["Jajuma_WebpImages/js/webpimages"],
    config: {
        mixins: {
            'mage/gallery/gallery': {
                'Jajuma_WebpImages/js/gallery/gallery-mixin': true
            },
            'Magento_Swatches/js/swatch-renderer': {
                'Jajuma_WebpImages/js/swatch-renderer-mixin': true
            },
        }
    }
};