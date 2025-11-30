'use strict';

define([
    'jquery',
    'rokanthemes/owl'
], function ($) {
    var DATA_KEY = 'rokanthemesNewproductInit';
    var DEFAULT_SELECTOR = '.owl';
    var DEFAULT_OPTIONS = {
        lazyLoad: true,
        autoPlay: false,
        items: 4,
        itemsDesktop: [1199, 4],
        itemsDesktopSmall: [980, 3],
        itemsTablet: [768, 2],
        itemsMobile: [479, 1],
        slideSpeed: 500,
        paginationSpeed: 500,
        rewindSpeed: 500,
        navigation: true,
        stopOnHover: true,
        pagination: false,
        scrollPerPage: true
    };

    function initialiseCarousel($carousel, options) {
        if (!$carousel.length || typeof $carousel.owlCarousel !== 'function') {
            return;
        }

        if ($carousel.data(DATA_KEY)) {
            return;
        }

        $carousel.owlCarousel(options);
        $carousel.data(DATA_KEY, true);
    }

    return function initNewproductCarousel(config, element) {
        var $root = $(element);
        var selector = config && config.carouselSelector ? config.carouselSelector : DEFAULT_SELECTOR;
        var options = $.extend(true, {}, DEFAULT_OPTIONS, config && config.owl);

        if (!$root.length) {
            return;
        }

        if ($root.data(DATA_KEY)) {
            return;
        }

        $root.find(selector).each(function iterateCarousels() {
            initialiseCarousel($(this), options);
        });

        $root.data(DATA_KEY, true);
    };
});
