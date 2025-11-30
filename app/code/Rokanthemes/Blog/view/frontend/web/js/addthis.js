'use strict';

define([
    'jquery'
], function ($) {
    var scriptDeferred;

    function loadScript(url) {
        if (!scriptDeferred || scriptDeferred.state() === 'rejected') {
            scriptDeferred = $.Deferred(function (defer) {
                var script = document.createElement('script');

                script.async = true;
                script.src = url;
                script.onload = function () {
                    defer.resolve(window.addthis || null);
                };
                script.onerror = function (event) {
                    scriptDeferred = null;
                    defer.reject(event);
                };

                document.head.appendChild(script);
            });
        }

        return scriptDeferred.promise();
    }

    function buildUrl(options) {
        var protocol = options.protocol || 'https:';
        var url = protocol + '//s7.addthis.com/js/250/addthis_widget.js';

        if (options.pubId) {
            url += '#pubid=' + encodeURIComponent(options.pubId);
        }

        return url;
    }

    function applyConfig(options) {
        var globalConfig = window.addthis_config = window.addthis_config || {};

        if (typeof options.trackClickback === 'boolean') {
            globalConfig.data_track_clickback = options.trackClickback;
        }

        if (options.language) {
            globalConfig.ui_language = options.language;
        }
    }

    function refreshToolbox(instance) {
        if (instance && typeof instance.toolbox === 'function') {
            instance.toolbox('.addthis_toolbox');
        }

        if (instance && typeof instance.layers === 'object' && typeof instance.layers.refresh === 'function') {
            instance.layers.refresh();
        }
    }

    return function initAddThis(config, element) {
        var options = $.extend({
            pubId: '',
            language: 'en',
            trackClickback: false,
            protocol: 'https:'
        }, config || {});

        applyConfig(options);

        loadScript(buildUrl(options)).done(function (addthis) {
            if (addthis && typeof addthis.init === 'function') {
                addthis.init();
            }

            refreshToolbox(addthis);
        });
    };
});
