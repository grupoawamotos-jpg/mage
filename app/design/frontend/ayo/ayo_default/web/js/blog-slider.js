'use strict';

define([
    'jquery',
    'rokanthemes/owl'
], function ($) {
    function initCarousel($element, config) {
        if (!$element.length || typeof $element.owlCarousel !== 'function') {
            return;
        }

        if ($element.data('owl-initialized')) {
            return;
        }

        var defaults = {
            lazyLoad: true,
            navigation: false,
            pagination: false,
            addClassActive: true
        };

        var options = $.extend(true, {}, defaults, config || {});

        options.navigation = !!options.navigation;
        options.pagination = !!options.pagination;

        $element.owlCarousel(options);
        $element.data('owl-initialized', true);
    }

    return function initBlogSlider(config, element) {
        var $scope = $(element);
        var selector = config.carouselSelector || '.owl';
        var $carousel = $scope.find(selector);

        initCarousel($carousel, config.options);
    };
});
