'use strict';

define([
    'require'
], function (require) {
    var bootstrapLoaded = false;

    return function initBootstrap(config) {
        var options = config || {};

        if (!options.loadBootstrap || bootstrapLoaded) {
            return;
        }

        bootstrapLoaded = true;
        require(['Rokanthemes_All/lib/bootstrap/js/bootstrap.min']);
    };
});
