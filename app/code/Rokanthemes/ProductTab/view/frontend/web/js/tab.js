'use strict';

define([
    'jquery',
    'rokanthemes/owl'
], function ($) {
    var DATA_KEY = 'rokanthemesProductTabInit';

    function normaliseOptions(options) {
        return $.extend(true, {
            lazyLoad: true,
            stopOnHover: true,
            pagination: false
        }, options || {});
    }

    function resolveTarget($root, targetId, fallback) {
        var selector;
        var $target;

        if (!targetId) {
            return fallback || $();
        }

        selector = targetId.charAt(0) === '#' ? targetId : '#' + targetId;
        $target = $root.find(selector);

        if (!$target.length) {
            return fallback || $();
        }

        return $target;
    }

    return function initProductTab(config, element) {
        var $root = $(element);
        var tabSelector = config && config.tabSelector ? config.tabSelector : 'ul.tabs li';
        var contentSelector = config && config.contentSelector ? config.contentSelector : '.tab_content';
        var tabAttribute = config && config.tabAttribute ? config.tabAttribute : 'rel';
        var activeClass = config && config.activeClass ? config.activeClass : 'active';
        var animateClass = config && config.animateClass ? config.animateClass : null;
        var carouselSelector = config && config.carouselSelector ? config.carouselSelector : null;
        var $tabs = $root.find(tabSelector);
        var $contents = $root.find(contentSelector);

        if (!$root.length || !$tabs.length || !$contents.length) {
            return;
        }

        if ($root.data(DATA_KEY)) {
            return;
        }
        $root.data(DATA_KEY, true);

        function showContent(targetId, withAnimation) {
            var $fallback = $contents.first();
            var $target = resolveTarget($root, targetId, $fallback);

            $contents.hide();
            if (animateClass) {
                $contents.removeClass(animateClass);
            }

            if (!$target.length) {
                return;
            }

            if (animateClass && withAnimation) {
                $target.addClass(animateClass);
            }

            $target.fadeIn();
        }

        $tabs.removeClass(activeClass);

        if ($tabs.length) {
            $tabs.first().addClass(activeClass);
        }

        showContent($tabs.first().attr(tabAttribute), false);

        $tabs.on('click', function (event) {
            var $tab = $(this);
            var targetId = $tab.attr(tabAttribute);

            event.preventDefault();

            if (!targetId) {
                return;
            }

            $tabs.removeClass(activeClass);
            $tab.addClass(activeClass);
            showContent(targetId, true);
        });

        if (carouselSelector) {
            $root.find(carouselSelector).each(function () {
                var $carousel = $(this);
                var options;

                if (typeof $carousel.owlCarousel !== 'function') {
                    return;
                }

                if ($carousel.data('product-tab-carousel')) {
                    return;
                }

                options = normaliseOptions(config && config.owl);
                $carousel.owlCarousel(options);
                $carousel.data('product-tab-carousel', true);
            });
        }
    };
});
