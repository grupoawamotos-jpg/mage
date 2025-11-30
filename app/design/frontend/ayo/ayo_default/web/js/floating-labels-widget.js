/**
 * Floating Labels Widget
 */
define(['jquery'], function ($) {
    'use strict';

    $.widget('mage.floatingLabels', {
        /**
         * Widget creation
         * @private
         */
        _create: function () {
            this._initFloatingLabels();
        },

        /**
         * Initialize floating labels
         * @private
         */
        _initFloatingLabels: function () {
            var selectors = 'input:not([type="hidden"]):not([type="checkbox"]):not([type="radio"]), textarea';
            
            $(document).on('focus', selectors, function () {
                var $input = $(this);
                var $field = $input.closest('.field');
                $field.addClass('is-focused floating-label-ready');
            });

            $(document).on('blur', selectors, function () {
                var $input = $(this);
                var $field = $input.closest('.field');
                $field.removeClass('is-focused');
                
                if ($input.val()) {
                    $field.addClass('has-value');
                } else {
                    $field.removeClass('has-value');
                }
            });

            // Initialize fields that already have values
            $(selectors).each(function () {
                var $input = $(this);
                var $field = $input.closest('.field');
                
                if ($input.val()) {
                    $field.addClass('has-value floating-label-ready');
                }
            });
        }
    });

    return $.mage.floatingLabels;
});
