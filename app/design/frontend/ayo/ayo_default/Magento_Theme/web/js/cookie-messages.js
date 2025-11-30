define([
    'jquery',
    'ko'
], function ($, ko) {
    'use strict';
    // Create a global observable array if it does not already exist
    if (typeof window.cookieMessagesObservable === 'undefined') {
        window.cookieMessagesObservable = ko.observableArray([]);
    }
    return window.cookieMessagesObservable;
});