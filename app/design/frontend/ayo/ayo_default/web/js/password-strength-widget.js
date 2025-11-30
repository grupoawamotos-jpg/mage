/**
 * Password Strength Widget
 */
define(['jquery', 'mage/translate'], function ($, $t) {
    'use strict';

    $.widget('mage.passwordStrength', {
        options: {
            minLength: 8
        },

        /**
         * Widget creation
         * @private
         */
        _create: function () {
            var self = this;
            
            this.element.on('input', function () {
                self._checkStrength($(this).val());
            });
        },

        /**
         * Check password strength
         * @param {string} password
         * @private
         */
        _checkStrength: function (password) {
            var strength = 0;
            var $field = this.element.closest('.field');
            var $meter = $field.find('.password-strength-meter');

            if (!$meter.length) {
                $meter = $('<div class="password-strength-meter"><div class="meter-bar"></div><span class="meter-text"></span></div>');
                $field.append($meter);
            }

            var $bar = $meter.find('.meter-bar');
            var $text = $meter.find('.meter-text');

            if (password.length >= this.options.minLength) strength++;
            if (password.length >= 12) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/\d/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;

            $bar.removeClass('weak medium strong very-strong');

            if (password.length === 0) {
                $bar.css('width', '0%');
                $text.text('');
            } else if (strength <= 1) {
                $bar.addClass('weak').css('width', '25%');
                $text.text($t('Weak'));
            } else if (strength === 2) {
                $bar.addClass('medium').css('width', '50%');
                $text.text($t('Medium'));
            } else if (strength === 3) {
                $bar.addClass('strong').css('width', '75%');
                $text.text($t('Strong'));
            } else {
                $bar.addClass('very-strong').css('width', '100%');
                $text.text($t('Very Strong'));
            }
        }
    });

    return $.mage.passwordStrength;
});
