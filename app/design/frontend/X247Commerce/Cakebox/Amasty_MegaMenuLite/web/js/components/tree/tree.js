/**
 *  Amasty Category Tree UI Component
 */

define([
    'ko',
    'uiComponent',
    'underscore',
    'ammenu_helpers'
], function (ko, Component, _, helpers) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Amasty_MegaMenuLite/components/tree/tree',
            templates: {
                title: 'Amasty_MegaMenuLite/components/tree/title',
                active_level: 'Amasty_MegaMenuLite/components/tree/active_level'
            },
            imports: {
                root_templates: 'index = ammenu_wrapper:templates',
                color_settings: 'index = ammenu_wrapper:color_settings',
                icons: 'index = ammenu_wrapper:icons',
                is_icons_available: 'index = ammenu_wrapper:is_icons_available',
                hide_view_all_link: 'index = ammenu_wrapper:hide_view_all_link'
            }
        },

        /**
         * Init Target elements method
         *
         * @public
         * @params {Object} data - current data {activeLevel, columns}
         * @return {void}
         */
        init: function (data) {
            this._initElems(data.activeLevel);
            this.setCurrentColor(data.activeLevel, this.color_settings.submenu_text);
            data.activeLevel = ko.observable(data.activeLevel);
            data.activeLevel().isSubmenuVisible(false);
            helpers.setItemFocus(data.activeLevel().parent);
            data.activeLevel.subscribe(function (elem) {
                console.log(elem);
                helpers.setItemFocus(elem);
            });
        },

        /**
         * Set prev level from current active level parent
         *
         * @public
         * @params {Object} activeLevel - current active level
         * @return {Boolean} for stop or continuous propagation
         */
        setPreviousLevel: function (activeLevel) {
            if (activeLevel().level() > 1) {
                this.setCurrentColor(activeLevel(), activeLevel().base_color);
                activeLevel(activeLevel().parent);

                return false;
            }

            return true;
        },

        /**
         * Set next level from current active level elems
         *
         * @public
         * @params {Object} activeLevel - current active level
         * @params {Object} elem - target elem
         * @return {Boolean} for continuous propagation if this link
         */
        setNextLevel: function (activeLevel, elem) {
            console.log( activeLevel );
            console.log( elem );
            this.setCurrentColor(elem, this.color_settings.submenu_text);
            activeLevel(elem);

            return false;
        },

        /**
         * Menu item hover handler
         *
         * @public
         * @params {Object} elem
         * @return {void}
         */
        onMouseenter: function (elem) {
            this.setCurrentColor(elem, this.color_settings.submenu_text_hover);
        },

        /**
         * Menu item mouse leave handler
         *
         * @public
         * @params {Object} elem
         * @return {void}
         */
        onMouseleave: function (elem) {
            this.setCurrentColor(elem, elem.base_color);
        },

        /**
         * Set Menu Elem Target Color
         *
         * @public
         * @params {Object} elem
         * @params {String} color
         * @return {void}
         */
        setCurrentColor: function (elem, color) {

            if (elem.current) {
                elem.color(this.color_settings.current_category_color);
            } else {
                elem.color(color);
            }
        },

        /**
         * Init Target elements method
         *
         * @private
         * @params {Object} elems
         * @return {void}
         */
        _initElems: function (element) {
            var self = this;

            _.each(element.elems, function (elem) {
                if (elem.elems.length) {
                    self._initElems(elem);
                }

                self._initElem(elem);
            });

            if (element.elems.length && !this.hide_view_all_link) {
                helpers.initAllItemLink(element, this.color_settings.third_level_menu);
            }

            self._initElem(element);
        },

        /**
         * Init Target element method
         *
         * @params {Object} elem
         * @return {void}
         */
        _initElem: function (elem) {
            elem.isSubmenuVisible = ko.observable(elem.elems.length && !elem.hide_content);
            this.setCurrentColor(elem, this.color_settings.third_level_menu);
            elem.base_color = this.color_settings.third_level_menu;
        },

        toggleItem: function(elem) {
            for (var i = 0; i < elem.elems.length; i++) {
                if ( elem.elems[i].level() < 3 ) {
                    elem.elems[i].isSubmenuVisible(false);
                }
            }
            if ( elem.isSubmenuVisible() ) {
                elem.isSubmenuVisible(false);
                elem.isActive(false);
            } else {
                elem.isSubmenuVisible(true);
                elem.isActive(true);
            }
        }
    });
});
