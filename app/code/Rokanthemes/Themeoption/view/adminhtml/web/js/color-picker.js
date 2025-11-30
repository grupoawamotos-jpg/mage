'use strict';

define([
    'jquery'
], function ($) {
    var loaderPromise;

    function loadScript(src) {
        var deferred = $.Deferred();
        var script = document.createElement('script');

        script.async = true;
        script.src = src;
        script.onload = function () {
            deferred.resolve();
        };
        script.onerror = function () {
            deferred.reject();
        };

        document.head.appendChild(script);

        return deferred.promise();
    }

    function ensureScript(src) {
        if (!loaderPromise) {
            loaderPromise = loadScript(src);
        }

        return loaderPromise;
    }

    return function initColorPicker(config) {
        var options = config || {};
        var src = options.src;
        var selector = options.selector || '.jscolor';

        if (!src) {
            return;
        }

        ensureScript(src).done(function () {
            if (window.jscolor && typeof window.jscolor.installByClassName === 'function') {
                var className = selector.charAt(0) === '.' ? selector.slice(1) : selector;
                window.jscolor.installByClassName(className);
            }
        });
    };
});
