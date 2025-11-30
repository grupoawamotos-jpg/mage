'use strict';

define([
    'jquery',
    'rokanthemes/owl'
], function ($) {
    function toBool(value) {
        return value === true || value === 'true' || value === 1 || value === '1';
    }

    function toInt($el, attr, fallback) {
        var value = parseInt($el.data(attr), 10);

        return isNaN(value) ? fallback : value;
    }

    function buildOptions($carousel, config) {
        var defaults = {
            lazyLoad: true,
            navigation: false,
            pagination: toBool($carousel.data('dot')),
            autoPlay: toBool($carousel.data('autoplay')),
            stopOnHover: toBool($carousel.data('pauonhover')),
            rtl: toBool($carousel.data('rtl')),
            afterAction: function () {
                if (this.$owlItems && this.$owlItems.length) {
                    this.$owlItems.removeClass('first-active');
                    this.$owlItems.eq(this.currentItem).addClass('first-active');
                }
            }
        };

        var options = $.extend(true, {}, defaults, config || {});
        var autoplayTimeout = toInt($carousel, 'autoplay-timeout', null);

        if (autoplayTimeout) {
            options.autoPlay = autoplayTimeout;
        }

        var mobileItems = toInt($carousel, 'mobile-items', 1);
        var tabletSmallItems = toInt($carousel, 'tablet-small-items', 2);
        var tabletItems = toInt($carousel, 'tablet-items', 3);
        var portraitItems = toInt($carousel, 'portrait-items', 4);
        var largeItems = toInt($carousel, 'large-items', 5);
        var largeMaxItems = toInt($carousel, 'large-max-items', largeItems);

        options.items = largeItems;
        options.itemsDesktop = [1200, largeMaxItems];
        options.itemsDesktopSmall = [992, portraitItems];
        options.itemsTablet = [768, tabletItems];
        options.itemsTabletSmall = [640, tabletSmallItems];
        options.itemsMobile = [480, mobileItems];

        if ($carousel.data('loop') !== undefined) {
            options.rewindNav = !toBool($carousel.data('loop'));
        }

        return options;
    }

    return function initBrandCarousel(config, element) {
        var $carousel = $(element);
        var $container = $carousel.closest('.block-content');
        var options;

        if (!$carousel.length || typeof $carousel.owlCarousel !== 'function') {
            return;
        }

        if ($carousel.data('brand-carousel-initialized')) {
            return;
        }

        options = buildOptions($carousel, config && config.owl);

        $carousel.owlCarousel(options);
        $carousel.data('brand-carousel-initialized', true);

        var $prev = $container.find('.owl-left');
        var $next = $container.find('.owl-right');

        if ($prev.length) {
            $prev.on('click.brandCarousel', function (event) {
                event.preventDefault();
                $carousel.trigger('owl.prev');
            });
        }

        if ($next.length) {
            $next.on('click.brandCarousel', function (event) {
                event.preventDefault();
                $carousel.trigger('owl.next');
            });
        }
    };
});
