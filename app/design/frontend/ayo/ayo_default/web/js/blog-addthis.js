'use strict';

define([
    'jquery'
], function ($) {
    var scriptLoaded = null;

    function loadScript(url) {
        if (scriptLoaded) {
            return scriptLoaded;
        }

        scriptLoaded = $.Deferred();
        var script = document.createElement('script');

        script.async = true;
        script.src = url;
        script.onload = function () {
            scriptLoaded.resolve();
        };
        script.onerror = function () {
            scriptLoaded.reject(new Error('Failed to load AddThis script.'));
        };

        document.head.appendChild(script);
        return scriptLoaded.promise();
    }

    return function initAddThis(config) {
        var settings = $.extend(true, {
            config: {
                data_track_clickback: false
            },
            scriptUrl: 'https://s7.addthis.com/js/250/addthis_widget.js',
            pubId: ''
        }, config || {});

        if (settings.pubId) {
            settings.scriptUrl += '#pubid=' + encodeURIComponent(settings.pubId);
        }

        window.addthis_config = settings.config;

        return loadScript(settings.scriptUrl);
    };
});
