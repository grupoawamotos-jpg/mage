/**
 * Accessibility Widget
 */
define(['jquery'], function ($) {
    'use strict';

    $.widget('mage.accessibility', {
        /**
         * Widget creation
         * @private
         */
        _create: function () {
            this._initAriaLabels();
            this._initKeyboardNavigation();
        },

        /**
         * Initialize ARIA labels
         * @private
         */
        _initAriaLabels: function () {
            $('input:not([aria-label]), textarea:not([aria-label])').each(function () {
                var $input = $(this);
                var $label = $('label[for="' + $input.attr('id') + '"]');
                var placeholder = $input.attr('placeholder');

                if ($label.length) {
                    $input.attr('aria-label', $label.text().trim());
                } else if (placeholder) {
                    $input.attr('aria-label', placeholder);
                }
            });

            $('.fieldset, .field-group').attr('role', 'group');
        },

        /**
         * Initialize keyboard navigation
         * @private
         */
        _initKeyboardNavigation: function () {
            $(document).on('keydown', function (e) {
                if (e.key === 'Tab') {
                    $('body').addClass('keyboard-navigation');
                }
            });

            $(document).on('mousedown', function () {
                $('body').removeClass('keyboard-navigation');
            });
        }
    });

    return $.mage.accessibility;
});
