/**
 * Character Counter Widget
 */
define(['jquery', 'mage/translate'], function ($, $t) {
    'use strict';

    $.widget('mage.charCounter', {
        options: {
            warningThreshold: 20
        },

        /**
         * Widget creation
         * @private
         */
        _create: function () {
            var self = this;
            var maxLength = this.element.attr('maxlength');

            if (!maxLength) {
                return;
            }

            this._createCounter();
            this._updateCounter();

            this.element.on('input', function () {
                self._updateCounter();
            });
        },

        /**
         * Create counter element
         * @private
         */
        _createCounter: function () {
            var $field = this.element.closest('.field');
            this.$counter = $('<div class="char-counter"><span class="current">0</span> / <span class="max"></span></div>');
            $field.append(this.$counter);
            this.$counter.find('.max').text(this.element.attr('maxlength'));
        },

        /**
         * Update counter
         * @private
         */
        _updateCounter: function () {
            var current = this.element.val().length;
            var max = parseInt(this.element.attr('maxlength'), 10);
            var remaining = max - current;

            this.$counter.find('.current').text(current);
            this.$counter.toggleClass('warning', remaining <= this.options.warningThreshold);
        }
    });

    return $.mage.charCounter;
});
