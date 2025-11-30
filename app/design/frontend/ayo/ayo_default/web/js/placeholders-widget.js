/**
 * Placeholders Widget - Brazilian specific placeholders
 */
define(['jquery'], function ($) {
    'use strict';

    $.widget('mage.placeholders', {
        /**
         * Widget creation
         * @private
         */
        _create: function () {
            var placeholders = {
                'input[name*="telephone" i], input[name*="telefone" i]': '(00) 00000-0000',
                'input[name*="postcode" i], input[name*="cep" i]': '00000-000',
                'input[name*="cpf" i]': '000.000.000-00',
                'input[name*="cnpj" i]': '00.000.000/0000-00',
                'input[name*="taxvat" i]': 'CPF ou CNPJ'
            };

            $.each(placeholders, function (selector, placeholder) {
                $(selector).each(function () {
                    var $input = $(this);
                    if (!$input.attr('placeholder')) {
                        $input.attr('placeholder', placeholder);
                    }
                });
            });
        }
    });

    return $.mage.placeholders;
});
