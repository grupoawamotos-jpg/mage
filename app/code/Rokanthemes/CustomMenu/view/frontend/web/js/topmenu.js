'use strict';

define([
    'jquery',
    'rokanthemes/custommenu'
], function ($) {
    function initialiseMenu($menu) {
        if (typeof $menu.CustomMenu === 'function') {
            $menu.CustomMenu();
            return;
        }

        if (typeof $.fn.CustomMenu === 'function') {
            $.fn.CustomMenu.call($menu);
        }
    }

    return function initCustomMenu(config, element) {
        var $menu = $(element);

        if (!$menu.length) {
            return;
        }

        if ($menu.data('custommenu-initialized')) {
            return;
        }

        initialiseMenu($menu);
        $menu.data('custommenu-initialized', true);
    };
});
