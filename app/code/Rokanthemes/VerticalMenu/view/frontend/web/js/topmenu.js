'use strict';

define([
    'jquery',
    'rokanthemes/verticalmenu'
], function ($) {
    function initialiseMenu($menu) {
        if (typeof $menu.VerticalMenu === 'function') {
            $menu.VerticalMenu();
            return;
        }

        if (typeof $.fn.VerticalMenu === 'function') {
            $.fn.VerticalMenu.call($menu);
        }
    }

    return function initVerticalMenu(config, element) {
        var $menu = $(element);

        if (!$menu.length) {
            return;
        }

        if ($menu.data('verticalmenu-initialized')) {
            return;
        }

        initialiseMenu($menu);
        $menu.data('verticalmenu-initialized', true);
    };
});
