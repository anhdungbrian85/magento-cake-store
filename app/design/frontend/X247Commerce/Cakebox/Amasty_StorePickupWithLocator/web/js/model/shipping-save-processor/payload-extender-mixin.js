define([
    'underscore',
    'uiRegistry',
    'mage/utils/wrapper',
    'Amasty_StorePickupWithLocator/js/model/pickup',
    'Amasty_StorePickupWithLocator/js/model/shipping-save-processor/data-preparer'
], function (_, registry, wrapper, pickup, dataPreparer) {
    'use strict';

    return function (payloadExtender) {
        return wrapper.wrap(payloadExtender, function (original, payload) {
            var payloadOriginal = original(payload),
                payloadWithPickupInfo = payloadOriginal,
                pickupInfo;
            pickupInfo = registry.get('checkoutProvider').get('block-store-locator').amstorepickup;
            if (pickupInfo && pickupInfo['am_pickup_store'] && pickupInfo['am_pickup_store'].id) {
                pickupInfo['am_pickup_store'] = pickupInfo['am_pickup_store'].id;
            } else {
                if (window.selectedStoreLocatorId) {
                    pickupInfo['am_pickup_store'] = selectedStoreLocatorId;
                }
            }

            if (_.isUndefined(payloadWithPickupInfo.addressInformation.extension_attributes)) {
                payloadWithPickupInfo.addressInformation.extension_attributes = {};
            }
            console.log('Payload extender mixin pickupInfo', pickupInfo);
            if (pickupInfo) {
                pickupInfo = { am_pickup: dataPreparer.prepareData(pickupInfo)};
                _.extend(payloadWithPickupInfo.addressInformation.extension_attributes, pickupInfo);
            }

            return payloadWithPickupInfo;
        });
    };
});
