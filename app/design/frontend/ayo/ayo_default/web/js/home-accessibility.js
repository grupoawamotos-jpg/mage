require(['jquery'], function ($) {
    'use strict';

    function enhanceSliderNavigation() {
        var $sliders = $('.home-slider, .rokan-slider, .ayo-home-slider');

        if (!$sliders.length) {
            return;
        }

        $sliders.each(function (index) {
            var $slider = $(this);
            var sliderId = $slider.attr('id') || 'home-slider-' + index;
            var ariaLabel = $slider.data('aria-label') || 'Destaques principais';

            $slider.attr({
                id: sliderId,
                role: 'region',
                'aria-label': ariaLabel
            });

            $slider.find('a, button').attr('tabindex', 0);
        });
    }

    function bindSkipLinks() {
        var $skipLinks = $('.skip-link');

        if (!$skipLinks.length) {
            return;
        }

        $skipLinks.on('click', function (event) {
            var targetSelector = $(this).attr('href');

            if (!targetSelector || targetSelector.charAt(0) !== '#') {
                return;
            }

            var $target = $(targetSelector);

            if ($target.length) {
                event.preventDefault();
                $target.attr('tabindex', -1).focus();
            }
        });
    }

    $(function () {
        enhanceSliderNavigation();
        bindSkipLinks();
    });
});
