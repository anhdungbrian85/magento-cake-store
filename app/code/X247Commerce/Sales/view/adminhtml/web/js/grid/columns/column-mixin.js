/**
 * @copyright  2023 247Commerce
 */

define([], function () {
    'use strict';

    var mixin = {
        staffEnabledSortingFields: window.staffEnabledSortingFields,
        isStaff: window.isStaff,

        /**
         *
         * @param enable
         * @returns {mixin|*}
         */
        sort: function (enable) {
            if (!this.isStaff) {
                return this._super(enable);
            }
            if (!this.staffEnabledSortingFields.include(this.label)) {
                return this;
            }

            return this._super(enable);
        },
    };

    return function (target) {
        return target.extend(mixin);
    }
});
