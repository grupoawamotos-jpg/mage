'use strict';

define([
    'jquery'
], function ($) {
    var SECOND = 1;
    var MINUTE = 60;
    var HOUR = 3600;
    var DAY = 86400;

    function pad(value) {
        return value < 10 ? '0' + value : String(value);
    }

    function format(parts, labels) {
        return '' +
            '<span class="countdown-p-p-c-section">' +
            ' <span class="countdown-amount-p-p-c">' + parts.days + '</span>' +
            ' <span class="countdown-label-p-p-c">' + labels.days + '</span> ' +
            '</span>' +
            ' <span class="countdown-p-p-c-section">' +
            ' <span class="countdown-amount-p-p-c">' + parts.hours + '</span>' +
            ' <span class="countdown-label-p-p-c">' + labels.hours + '</span> ' +
            '</span>' +
            ' <span class="countdown-p-p-c-section">' +
            ' <span class="countdown-amount-p-p-c">' + parts.minutes + '</span>' +
            ' <span class="countdown-label-p-p-c">' + labels.minutes + '</span> ' +
            '</span>' +
            ' <span class="countdown-p-p-c-section">' +
            ' <span class="countdown-amount-p-p-c">' + parts.seconds + '</span>' +
            ' <span class="countdown-label-p-p-c">' + labels.seconds + '</span> ' +
            '</span>';
    }

    function getParts(totalSeconds) {
        var remaining = totalSeconds;
        var days = Math.floor(remaining / DAY);
        remaining -= days * DAY;

        var hours = Math.floor(remaining / HOUR);
        remaining -= hours * HOUR;

        var minutes = Math.floor(remaining / MINUTE);
        remaining -= minutes * MINUTE;

        var seconds = Math.max(0, Math.floor(remaining / SECOND));

        return {
            days: pad(days),
            hours: pad(hours),
            minutes: pad(minutes),
            seconds: pad(seconds)
        };
    }

    function parseDate(value) {
        if (!value) {
            return null;
        }

        var parsed = Date.parse(value);

        if (isNaN(parsed)) {
            return null;
        }

        return new Date(parsed);
    }

    return function initProductCountdown(config, element) {
        var settings = $.extend(true, {
            countdownSelector: '',
            endDate: null,
            startDate: null,
            currentTime: null,
            labels: {
                days: 'Days',
                hours: 'Hours',
                minutes: 'Minutes',
                seconds: 'Seconds'
            },
            finishMessage: '',
            step: -1
        }, config || {});

        var $root = $(element);
        var $target = settings.countdownSelector ? $root.find(settings.countdownSelector) : $root;
        var endDate = parseDate(settings.endDate);
        var current = parseDate(settings.currentTime) || new Date();
        var startDate = parseDate(settings.startDate);
        var step = parseInt(settings.step, 10);
        var timerId;

        if (!$root.length || !$target.length || !endDate || isNaN(step) || step === 0) {
            return;
        }

        function clearTimer() {
            if (timerId) {
                clearTimeout(timerId);
                timerId = null;
            }
        }

        function stopCountdown() {
            clearTimer();
            if (settings.finishMessage) {
                $target.html(settings.finishMessage);
            } else {
                $target.empty();
            }
        }

        function tick(remainingSeconds) {
            var nextValue = remainingSeconds + step;

            if (remainingSeconds < 0) {
                stopCountdown();
                return;
            }

            $target.html(format(getParts(remainingSeconds), settings.labels));

            timerId = setTimeout(function () {
                tick(nextValue);
            }, Math.abs(step) * 1000);
        }

        if (startDate && current < startDate) {
            stopCountdown();
            return;
        }

        var initialSeconds = Math.floor((endDate.getTime() - current.getTime()) / SECOND);

        tick(initialSeconds);
    };
});
