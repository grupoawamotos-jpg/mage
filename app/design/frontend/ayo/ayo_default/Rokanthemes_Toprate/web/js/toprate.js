define([
    'jquery',
    'rokanthemes/owl'
], function ($) {
    'use strict';

    var DATA_KEY = 'rokanthemesToprateInit';
    var DEFAULT_SELECTOR = '.owl';
    var DEFAULT_OPTIONS = {
        lazyLoad: true,
        autoPlay: false,
        items: 1,
        itemsDesktop: [1199, 1],
        itemsDesktopSmall: [991, 1],
        itemsTablet: [767, 2],
        itemsMobile: [479, 1],
        slideSpeed: 500,
        paginationSpeed: 500,
        rewindSpeed: 500,
        navigation: true,
        navigationText: ['<i class="fa fa-angle-left"></i>', '<i class="fa fa-angle-right"></i>'],
        stopOnHover: true,
        pagination: false,
        scrollPerPage: true
    };

    function initCarousel($carousel, options) {
        if (!$carousel.length || typeof $carousel.owlCarousel !== 'function') {
            return;
        }

        if ($carousel.data(DATA_KEY)) {
            return;
        }

        $carousel.owlCarousel(options);
        $carousel.data(DATA_KEY, true);
    }

    return function initialiseToprate(config, element) {
        var $root = $(element);
        var selector = config && config.carouselSelector ? config.carouselSelector : DEFAULT_SELECTOR;
        var options = $.extend(true, {}, DEFAULT_OPTIONS, config && config.owl);

        if (!$root.length) {
            return;
        }

        if ($root.data(DATA_KEY)) {
            return;
        }

        $root.find(selector).each(function initialiseEach() {
            initCarousel($(this), options);
        });

        $root.data(DATA_KEY, true);
    };
});
