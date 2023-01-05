/* eslint-disable no-mixed-operators */
/**
 * Amasty MegaMenu Wrapper UI Component
 *
 * @desc Component Mega Menu Lite Module
 */

define([
    'jquery',
    'ko',
    'uiComponent',
    'underscore',
    'ammenu_color_helper',
    'ammenu_helpers'
], function ($, ko, Component, _, colorHelper, helpers) {
    'use strict';

    return Component.extend({
        defaults: {
            components: {
                ammenu_sidebar_menu_wrapper: {
                    name: 'ammenu_sidebar_menu_wrapper',
                    component: 'Amasty_MegaMenuLite/js/sidebar_menu/sidebar_menu',
                    deps: ['index = ammenu_wrapper'],
                    enable_condition: function () {
                        return this.isMobile() || this.is_hamburger;
                    }
                },
                ammenu_submenu_wrapper: {
                    name: 'ammenu_submenu_wrapper',
                    component: 'uiComponent',
                    template: 'Amasty_MegaMenuLite/submenu/wrapper',
                    deps: ['index = ammenu_wrapper'],
                    imports: {
                        animation_time: 'index = ammenu_wrapper:animation_time',
                        color_settings: 'index = ammenu_wrapper:color_settings'
                    },
                    enable_condition: function () {
                        return !this.isMobile();
                    }
                },
                ammenu_submenu_simple: {
                    name: 'ammenu_submenu_simple',
                    component: 'Amasty_MegaMenuLite/js/submenu/simple',
                    deps: ['index = ammenu_wrapper'],
                    enable_condition: function () {
                        return !this.isMobile();
                    }
                },
                ammenu_hamburger_wrapper: {
                    name: 'ammenu_hamburger_wrapper',
                    component: 'Amasty_MegaMenuLite/js/hamburger_menu/hamburger_menu',
                    deps: ['index = ammenu_wrapper'],
                    enable_condition: function () {
                        return !this.isMobile() && this.is_hamburger;
                    }
                },
                ammenu_overlay_wrapper: {
                    name: 'ammenu_overlay_wrapper',
                    component: 'Amasty_MegaMenuLite/js/components/overlay',
                    deps: ['index = ammenu_hamburger_toggle'],
                    enable_condition: function () {
                        return this.isMobile() || this.is_hamburger;
                    }
                },
                ammenu_columns_wrapper: {
                    name: 'ammenu_columns_wrapper',
                    component: 'Amasty_MegaMenuLite/js/components/tree/columns',
                    deps: ['index = ammenu_wrapper'],
                    enable_condition: function () {
                        return !this.isMobile();
                    }
                },
                ammenu_tree_wrapper: {
                    name: 'ammenu_tree_wrapper',
                    component: 'Amasty_MegaMenuLite/js/components/tree/tree',
                    deps: ['index = ammenu_wrapper'],
                    enable_condition: function () {
                        return !this.isMobile();
                    }
                },
                ammenu_account_wrapper: {
                    name: 'ammenu_account_wrapper',
                    component: 'Amasty_MegaMenuLite/js/components/account',
                    deps: ['index = ammenu_wrapper'],
                    enable_condition: function () {
                        return this.isMobile() || this.is_hamburger;
                    }
                },
                ammenu_tabs_wrapper: {
                    name: 'ammenu_tabs_wrapper',
                    component: 'Amasty_MegaMenuLite/js/components/tabs',
                    deps: ['index = ammenu_wrapper'],
                    enable_condition: function () {
                        return this.isMobile() || this.is_hamburger;
                    }
                },
                ammenu_hamburger_toggle: {
                    name: 'ammenu_hamburger_toggle',
                    component: 'Amasty_MegaMenuLite/js/components/hamburger_toggle',
                    deps: ['index = ammenu_wrapper'],
                    enable_condition: function () {
                        return this.isMobile() || this.is_hamburger;
                    }
                }
            },
            template: 'Amasty_MegaMenuLite/wrapper',
            templates: {
                drill_wrapper: 'Amasty_MegaMenu/sidebar_menu/drill/wrapper',
                sidebar_type_switcher: 'Amasty_MegaMenuLite/sidebar_menu/type_switcher',
                greetings: 'Amasty_MegaMenuLite/components/greetings',
                item: 'Amasty_MegaMenuLite/components/item/wrapper',
                item_content: 'Amasty_MegaMenuLite/components/item/content',
                item_button: 'Amasty_MegaMenuLite/components/item/button',
                item_link: 'Amasty_MegaMenuLite/components/item/link',
                label: 'Amasty_MegaMenuLite/components/item/label',
                close_button: 'Amasty_MegaMenuLite/components/buttons/close',
                icon: 'Amasty_MegaMenuLite/components/icon',
                item_icon: 'Amasty_MegaMenuLite/components/item/icon',
                hamburger: 'Amasty_MegaMenuLite/hamburger_menu/top/wrapper',
                menu_title: 'Amasty_MegaMenuLite/sidebar_menu/title',
                hamburger_items: 'Amasty_MegaMenuLite/hamburger_menu/items',
                tree_active_level: 'Amasty_MegaMenuLite/components/tree/active_level',
                accordion: 'Amasty_MegaMenuLite/sidebar_menu/accordion/wrapper',
                expand: 'Amasty_MegaMenuLite/components/icons/expand'
            },
            icons: {
                create_account: 'Amasty_MegaMenuLite/components/icons/create_account',
                currency: 'Amasty_MegaMenuLite/components/icons/currency',
                exit: 'Amasty_MegaMenuLite/components/icons/exit',
                language: 'Amasty_MegaMenuLite/components/icons/language',
                settings: 'Amasty_MegaMenuLite/components/icons/settings',
                sign_in: 'Amasty_MegaMenuLite/components/icons/sign_in',
                user: 'Amasty_MegaMenuLite/components/icons/user',
                wishlist: 'Amasty_MegaMenuLite/components/icons/wishlist',
                chevron: 'Amasty_MegaMenuLite/components/icons/chevron',
                double_chevron: 'Amasty_MegaMenuLite/components/icons/double_chevron'
            },
            view_port: {
                height: $(window).height(),
                width: $(window).width()
            },
            settings: {
                delay: 100
            },
            maps: {
                id_index: {}
            },
            custom_item_count: 0
        },

        /**
         * @inheritDoc
         */
        initialize: function () {
            this._super();

            this.data.isRoot = true;

            this.initElems(this.data.elems, 0, this.data);
            this._generateBaseColors();
            helpers.mountComponents(this);

            return this;
        },

        /**
         * @inheritDoc
         */
        initObservable: function () {
            this._super()
                .observe({
                    isSticky: false,
                    isMobile: window.outerWidth <= this.mobile_menu_width,
                    topMenuActiveItem: null
                });

            this.isMobile.subscribe(function () {
                helpers.mountComponents(this);
            }.bind(this));

            window.addEventListener('resize', this._onScreenResize.bind(this));

            return this;
        },

        /**
         * Init Target elements method
         *
         * @param {Object} elems
         * @param {Number} level
         * @param {Object} parent
         * @return {void}
         */
        initElems: function (elems, level, parent) {
            var self = this;

            _.each(elems, function (elem) {
                self.initElem(elem, level, parent);

                if (elem.elems.length) {
                    self.initElems(elem.elems, level + 1, elem);
                }
            });
        },

        /**
         * Init Target element colors method
         *
         * @param {Object} elem
         * @return {void}
         */
        initElemColors: function (elem) {
            elem.color = ko.observable(
                elem.current ? this.color_settings.current_category_color : this.color_settings.main_menu_text
            );
            elem.base_color = elem.color();
        },

        /**
         * Init Target element method
         *
         * @param {Object} elem
         * @param {Number} level
         * @param {Object} parent
         * @return {void}
         */
        initElem: function (elem, level, parent) {
            elem.isActive = ko.observable(false);
            elem.rendered = ko.observable(false);
            elem.level = ko.observable(level);
            elem.isContentActive = ko.observable(false);
            elem.isSubmenuVisible = ko.observable(true);
            elem.additionalClasses = [];
            elem.isVisible = true;
            elem.isFocused = ko.observable(false);
            elem.column_count = ko.observable(elem.column_count);

            Object.defineProperty(elem, 'index', {
                get: function () {
                    return parent.elems.indexOf(elem) || 0;
                }
            });

            elem.isActive.subscribe(function (value) {
                if (value) {
                    helpers.setItemFocus(elem);
                }
            });

            if (!elem.is_category) {
                this._initCustomItem(elem);
            }

            if (level === 0) {
                this._initRoot(elem);
            }

            if (parent) {
                elem.parent = parent;
            }

            this.initElemColors(elem);
        },

        /**
         * Init root submenu element
         *
         * @param {Object} elem
         * @return {void}
         */
        _initRoot: function (elem) {
            this.maps.id_index[elem.id] = elem.index;
            elem.width_value = ko.observable(elem.width_value);
            elem.nodes = {};

            elem.isSubmenuVisible(
                !elem.submenu_type && elem.content && elem.content.trim().length > 7
                || elem.submenu_type && elem.type.value && !elem.hide_content && elem.elems.length
            );

            if (elem.width === 0) {
                elem.width_value('100%');
            }

            if (elem.width === 1) {
                elem.width_value('max-content');
            }

            if (elem.width_value() && elem.width === 2) {
                elem.width_value(elem.width_value() + 'px');
            }
        },

        /**
         * Init Custom item
         *
         * @param {Object} elem
         * @return {void}
         */
        _initCustomItem: function (elem) {
            if (
                elem.status === 2 && this.isMobile()
                || elem.status === 3 && !this.isMobile()
            ) {
                elem.isVisible = false;
            }

            this.custom_item_count += 1;
        },

        /**
         * Generating base color setting from base customers colors
         * @return {void}
         */
        _generateBaseColors: function () {
            this.color_settings
                .border = colorHelper.getLighten(this.color_settings.toggle_icon_color, 0.16);
            this.color_settings
                .third_level_menu = colorHelper.getAltered(this.color_settings.submenu_text, 0.2);
            this.color_settings
                .toggle_icon_color_hover = colorHelper.getDarken(this.color_settings.toggle_icon_color, 0.2);
            this.color_settings
                .toggle_icon_color_active = colorHelper.getDarken(this.color_settings.toggle_icon_color, 0.3);
            this.color_settings
                .hamburger_icon_color_hover = colorHelper.getDarken(this.color_settings.hamburger_icon_color, 0.2);
            this.color_settings
                .hamburger_icon_color_active = colorHelper.getDarken(this.color_settings.hamburger_icon_color, 0.3);
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
        },

        /**
         * Rotation and screen resize Event Handler Initialization
         *
         * @description update isMobile param via new resolution
         * @private
         * @return {void}
         */
        _onScreenResize: _.debounce(function () {
            this.isMobile(window.outerWidth <= this.mobile_menu_width);
        }, 300)
    });
});
