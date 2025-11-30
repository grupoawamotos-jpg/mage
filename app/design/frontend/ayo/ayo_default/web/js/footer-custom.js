define([
    'jquery',
    'rokanthemes/owl'
], function ($) {
    'use strict';

    var initialized = false;

    function initBrandCarousel(config) {
        var carouselConfig = config.carousel || {};
        var selector = carouselConfig.selector || '.block-content.brandowl-play > ul';
        var $list = $(selector);

        if (!$list.length || typeof $list.owlCarousel !== 'function') {
            return;
        }

        var options = $.extend(true, {
            lazyLoad: true,
            items: 7,
            itemsDesktop: [1366, 5],
            itemsDesktopSmall: [991, 3],
            itemsTablet: [767, 2],
            itemsMobile: [479, 1],
            navigation: true,
            afterAction: function () {
                this.$owlItems.removeClass('first-active');
                this.$owlItems.eq(this.currentItem).addClass('first-active');
            }
        }, carouselConfig.options || {});

        $list.owlCarousel(options);
    }

    function initAccordion(config) {
        var accordionConfig = config.accordion || {};
        var triggerSelector = accordionConfig.triggerSelector || '.velaFooterMenu h4.velaFooterTitle';
        var contentSelector = accordionConfig.contentSelector || '.velaContent';
        var breakpoint = parseInt(accordionConfig.breakpoint, 10);
        var namespace = '.footerCustom';
        var responsiveFlag = false;
        var $window = $(window);

        if (isNaN(breakpoint)) {
            breakpoint = 767;
        }

        function enableAccordion() {
            $(triggerSelector).on('click' + namespace, function (event) {
                $(this).toggleClass('active').parent().find(contentSelector).stop().slideToggle('medium');
                event.preventDefault();
            });
        }

        function disableAccordion() {
            $(triggerSelector).removeClass('active').off(namespace).parent().find(contentSelector).slideDown('fast');
        }

        function responsiveResize() {
            if ($window.width() <= breakpoint && !responsiveFlag) {
                enableAccordion();
                responsiveFlag = true;
            } else if ($window.width() > breakpoint && responsiveFlag) {
                disableAccordion();
                responsiveFlag = false;
            }
        }

        responsiveResize();
        $window.on('resize' + namespace, responsiveResize);
    }

    function run(config) {
        initBrandCarousel(config);
        initAccordion(config);
    }

    return function (config) {
        if (initialized) {
            return;
        }

        initialized = true;
        config = config || {};

        $(function () {
            run(config);
        });
    };
});
