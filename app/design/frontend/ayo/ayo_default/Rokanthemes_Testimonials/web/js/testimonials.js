define([
    'jquery',
    'rokanthemes/owl'
], function ($) {
    'use strict';

    function initCarousel($carousel, options) {
        if (!$carousel.length || typeof $carousel.owlCarousel !== 'function') {
            return;
        }

        if ($carousel.data('owl-initialized')) {
            return;
        }

        var defaults = {
            lazyLoad: true,
            items: 1,
            itemsCustom: [
                [0, 1],
                [480, 1],
                [768, 1],
                [992, 1],
                [1200, 1]
            ],
            pagination: false,
            navigation: false,
            navigationText: ['<i class="fa fa-angle-left"></i>', '<i class="fa fa-angle-right"></i>']
        };

        var settings = $.extend(true, {}, defaults, options || {});

        $carousel.owlCarousel(settings);
        $carousel.data('owl-initialized', true);
    }

    return function (config, element) {
        var $container = $(element);
        var selector = config && config.carouselSelector ? config.carouselSelector : '.wrap-item';
        var $carousel = $container.find(selector);

        initCarousel($carousel, config && config.owl);
    };
});
