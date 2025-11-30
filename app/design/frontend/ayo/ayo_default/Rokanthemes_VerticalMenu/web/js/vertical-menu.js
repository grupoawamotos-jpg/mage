'use strict';

define([
    'jquery',
    'rokanthemes/verticalmenu'
], function ($) {
    var defaults = {
        toggleSelector: '.togge-menu',
        titleSelector: '.title-category-dropdown',
        expandLinkSelector: '.expand-category-link',
        itemSelector: '.ui-menu-item.level0',
        otherItemClass: 'orther-link',
        limitItemShow: 10,
        responsiveBreakpoint: 1399,
        homeBodyClass: 'cms-index-index'
    };

    function initPlugin($nav) {
        if (typeof $nav.VerticalMenu === 'function') {
            $nav.VerticalMenu();
        }
    }

    function markExtraItems($nav, config) {
        var limit = parseInt(config.limitItemShow, 10) || defaults.limitItemShow;
        var $items = $nav.find(config.itemSelector);
        var $expandLink = $nav.find(config.expandLinkSelector);
        var $extraItems;

        if (!$items.length) {
            $expandLink.hide();
            return;
        }

        if (limit < 1) {
            $expandLink.hide();
            return;
        }

        $items.removeClass(config.otherItemClass).show();
        $expandLink.removeClass('expanding').off('.verticalMenu');

        $extraItems = $items.slice(limit - 1);

        if ($extraItems.length) {
            $extraItems.addClass(config.otherItemClass).hide();
            $expandLink.show();
            $expandLink.off('.verticalMenu').on('click.verticalMenu', function (event) {
                event.preventDefault();
                $(this).toggleClass('expanding');
                $extraItems.stop(true, true).slideToggle('slow');
            });
        } else {
            $expandLink.hide();
        }
    }

    function bindToggle($nav, config) {
        var $title = $nav.find(config.titleSelector);
        var $toggleMenu = $nav.find(config.toggleSelector);

        if (!$title.length || !$toggleMenu.length) {
            return;
        }

        $title.off('.verticalMenu').on('click.verticalMenu', function (event) {
            event.preventDefault();
            $toggleMenu.stop(true, true).slideToggle('slow');
            $(this).toggleClass('active');
        });
    }

    function applyHomeVisibility($nav, config) {
        var $toggleMenu = $nav.find(config.toggleSelector);
        var isHome = $('body').hasClass(config.homeBodyClass);

        if (!$toggleMenu.length) {
            return;
        }

        if (!isHome) {
            return;
        }

        if ($(window).width() >= config.responsiveBreakpoint) {
            $toggleMenu.show();
        } else {
            $toggleMenu.hide();
        }
    }

    return function initVerticalMenu(config, element) {
        var settings = $.extend(true, {}, defaults, config || {});
        var $nav = $(element);
        var $toggleMenu;

        if (!$nav.length) {
            return;
        }

        initPlugin($nav);

        $toggleMenu = $nav.find(settings.toggleSelector);
        if ($toggleMenu.length && !$toggleMenu.is(':visible')) {
            $toggleMenu.hide();
        }

        markExtraItems($nav, settings);
        bindToggle($nav, settings);
        applyHomeVisibility($nav, settings);

        var navId = $nav.attr('id') || ('verticalmenu-' + Math.random().toString(36).slice(2));
        var namespace = '.verticalMenuResize-' + navId;

        $(window).off('resize' + namespace).on('resize' + namespace, function () {
            initPlugin($nav);
            applyHomeVisibility($nav, settings);
        });
    };
});
