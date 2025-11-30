'use strict';

define([
    'jquery',
    'rokanthemes/timecircles'
], function ($) {
    function openSharePopup(event) {
        var $link = $(this);
        var width = parseInt($link.data('share-width'), 10) || 500;
        var height = parseInt($link.data('share-height'), 10) || 500;
        var target = $link.data('share-window') || 'share';

        event.preventDefault();
        window.open($link.attr('href'), target, 'width=' + width + ',height=' + height);
    }

    function initCountdowns($root, config) {
        var $countdowns = $root.find('.super-deal-countdown');

        if (!$countdowns.length || typeof $countdowns.TimeCircles !== 'function') {
            return;
        }

        var options = $.extend(true, {
            fg_width: 0.01,
            bg_width: 1.2,
            text_size: 0.07,
            circle_bg_color: '#ffffff'
        }, config || {});

        $countdowns.TimeCircles(options);
    }

    return function initSuperDeal(config, element) {
        var $root = $(element);

        if (!$root.length) {
            return;
        }

        if ($root.data('superdeal-initialized')) {
            return;
        }

        $root.data('superdeal-initialized', true);

        $root.on('click.superdealsShare', '[data-share-popup]', openSharePopup);
        initCountdowns($root, config && config.timeCircles);
    };
});
