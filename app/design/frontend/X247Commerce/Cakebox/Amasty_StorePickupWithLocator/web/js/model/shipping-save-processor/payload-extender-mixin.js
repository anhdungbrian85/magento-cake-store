define([
    'underscore',
    'uiRegistry',
    'mage/utils/wrapper',
    'Amasty_StorePickupWithLocator/js/model/pickup',
    'Amasty_StorePickupWithLocator/js/model/shipping-save-processor/data-preparer'
], function (_, registry, wrapper, pickup, dataPreparer) {
    'use strict';

    function formatDate(dateOrig)
    {
        if (dateOrig) {
            const toFragments = dateString => dateString ? dateString.split(/[-/]/) : dateString;
            const dateTo_mmddyyyy = ([date, month, year], divider = "/") => `${month}${divider}${date}${divider}${year}`;
            return dateTo_mmddyyyy(toFragments(dateOrig));
        }
        return dateOrig;
        
    }


    return function (payloadExtender) {
        return wrapper.wrap(payloadExtender, function (original, payload) {
            var payloadOriginal = original(payload),
                payloadWithPickupInfo = payloadOriginal,
                pickupInfo;

            if (pickup.isPickup()) {
                pickupInfo = registry.get('checkoutProvider').get('amstorepickup');
                
                if (pickupInfo && pickupInfo['am_pickup_store'] && pickupInfo['am_pickup_store'].id) {
                    pickupInfo['am_pickup_store'] = pickupInfo['am_pickup_store'].id;
                }

                if (_.isUndefined(payloadWithPickupInfo.addressInformation.extension_attributes)) {
                    payloadWithPickupInfo.addressInformation.extension_attributes = {};
                }

                if (pickupInfo) {
                    pickupInfo = { am_pickup: dataPreparer.prepareData(pickupInfo)};
                    var pickupInfoFormatTed = pickupInfo;
                    console.log(pickupInfo)
                    pickupInfoFormatTed['am_pickup']['date'] = formatDate(pickupInfoFormatTed['am_pickup']['date']);
                    console.log(pickupInfoFormatTed['am_pickup'])
                    _.extend(payloadWithPickupInfo.addressInformation.extension_attributes, pickupInfoFormatTed);
                    pickupInfoFormatTed = null;
                }
            }

            return payloadWithPickupInfo;
        });
    };
});
