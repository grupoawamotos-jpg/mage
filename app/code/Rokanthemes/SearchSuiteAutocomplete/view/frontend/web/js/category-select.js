'use strict';

define([
    'jquery',
    'rokanthemes/choose'
], function ($) {
    return function initCategorySelect(config, element) {
        var $element = $(element);
        var options = config && config.options ? config.options : {};

        if (!$element.length || typeof $element.chosen !== 'function') {
            return;
        }

        if ($element.data('chosen-initialized')) {
            $element.trigger('chosen:updated');
            return;
        }

        $element.chosen(options);
        $element.data('chosen-initialized', true);

        if (config && config.triggerFormSubmitOnChange) {
            $element.on('change.categorySelect', function () {
                var $form = $element.closest('form');

                if ($form.length) {
                    $form.trigger('submit');
                }
            });
        }
    };
});
