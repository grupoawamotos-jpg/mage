/**
 * Form Validation Styles Widget
 */
define(['jquery'], function ($) {
    'use strict';

    $.widget('mage.formValidationStyles', {
        options: {
            animateErrors: true
        },

        /**
         * Widget creation
         * @private
         */
        _create: function () {
            var self = this;

            this.element.on('blur', function () {
                var $input = $(this);
                var $field = $input.closest('.field');

                $field.removeClass('validation-passed validation-failed');

                if ($input.prop('required') || $input.hasClass('required')) {
                    if ($input.val() && !$input.hasClass('mage-error')) {
                        $field.addClass('validation-passed');
                    } else if (!$input.val()) {
                        $field.addClass('validation-failed');
                    }
                }
            });

            if (this.options.animateErrors) {
                this.element.on('invalid', function () {
                    var $field = $(this).closest('.field');
                    $field.addClass('shake');
                    setTimeout(function () {
                        $field.removeClass('shake');
                    }, 500);
                });
            }
        }
    });

    return $.mage.formValidationStyles;
});
