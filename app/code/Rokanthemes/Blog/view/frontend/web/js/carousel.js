'use strict';

define([
    'jquery',
    'rokanthemes/owl'
], function ($) {
    function normaliseOptions(options) {
        return $.extend(true, {
            lazyLoad: true,
            navigation: false,
            pagination: false,
            addClassActive: true,
            stopOnHover: true,
            scrollPerPage: true
        }, options || {});
    }

    return function initBlogCarousel(config, element) {
        var $carousel = $(element);
        var options;

        if (!$carousel.length || typeof $carousel.owlCarousel !== 'function') {
            return;
        }

        if ($carousel.data('blog-carousel-initialized')) {
            return;
        }

        options = normaliseOptions(config);

        $carousel.owlCarousel(options);
        $carousel.data('blog-carousel-initialized', true);
    };
});
