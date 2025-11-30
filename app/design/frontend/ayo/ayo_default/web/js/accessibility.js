define(['jquery'], function ($) {
    'use strict';
    return function () {
        // Expand/collapse vertical menu accessibility
        var toggle = $('.vertical-category-menu .vertical-menu-toggle');
        var list = $('#vertical-menu-list');
        if (toggle.length && list.length) {
            toggle.on('click keydown', function (e) {
                if (e.type === 'keydown' && !(e.key === 'Enter' || e.key === ' ')) { return; }
                e.preventDefault();
                var hidden = list.attr('hidden') !== undefined;
                if (hidden) {
                    list.removeAttr('hidden');
                    toggle.attr('aria-expanded', 'true');
                } else {
                    list.attr('hidden', '');
                    toggle.attr('aria-expanded', 'false');
                }
            });
        }
        // Submenus lazy reveal on focus/hover
        list.find('li.level0').each(function () {
            var item = $(this);
            var submenu = item.children('.submenu');
            if (!submenu.length) { return; }
            item.children('a').on('focus mouseenter', function () {
                submenu.removeAttr('hidden');
            });
            item.on('mouseleave', function () {
                submenu.attr('hidden', '');
            });
        });
    };
});
