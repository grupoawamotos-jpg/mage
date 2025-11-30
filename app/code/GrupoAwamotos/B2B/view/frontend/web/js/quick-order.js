/**
 * Quick Order Form JavaScript
 */
define([
    'jquery',
    'mage/translate',
    'jquery-ui-modules/widget'
], function ($, $t) {
    'use strict';

    $.widget('grupoawamotos.quickOrder', {
        options: {
            formSelector: '#quick-order-form',
            itemsContainer: '#quick-order-items',
            addRowBtn: '#add-row-btn',
            bulkInput: '#bulk-input',
            parseBulkBtn: '#parse-bulk-btn',
            clearBulkBtn: '#clear-bulk-btn',
            resultsSection: '#results-section',
            resultsContent: '#results-content'
        },

        rowIndex: 5,

        _create: function () {
            this._bindEvents();
        },

        _bindEvents: function () {
            var self = this;

            // Add row button
            $(this.options.addRowBtn).on('click', function () {
                self._addRow();
            });

            // Remove row button (delegated)
            $(this.options.itemsContainer).on('click', '.remove-row', function () {
                self._removeRow($(this).closest('.item-row'));
            });

            // Parse bulk input
            $(this.options.parseBulkBtn).on('click', function () {
                self._parseBulkInput();
            });

            // Clear bulk input
            $(this.options.clearBulkBtn).on('click', function () {
                $(self.options.bulkInput).val('');
                self._hideResults();
            });

            // Form submit with AJAX
            $(this.options.formSelector).on('submit', function (e) {
                e.preventDefault();
                self._submitForm();
            });

            // Auto-focus next SKU input on Enter
            $(this.options.itemsContainer).on('keypress', '.sku-input', function (e) {
                if (e.which === 13) {
                    e.preventDefault();
                    var nextRow = $(this).closest('.item-row').next('.item-row');
                    if (nextRow.length) {
                        nextRow.find('.sku-input').focus();
                    } else {
                        self._addRow();
                        setTimeout(function () {
                            $(self.options.itemsContainer).find('.item-row:last .sku-input').focus();
                        }, 100);
                    }
                }
            });
        },

        _addRow: function () {
            var template = '<div class="item-row" data-row="' + this.rowIndex + '">' +
                '<div class="col-sku">' +
                    '<input type="text" name="items[' + this.rowIndex + '][sku]" ' +
                           'class="input-text sku-input" ' +
                           'placeholder="' + $t('Ex: PECA-001') + '" autocomplete="off">' +
                '</div>' +
                '<div class="col-qty">' +
                    '<input type="number" name="items[' + this.rowIndex + '][qty]" ' +
                           'class="input-text qty-input" value="1" min="1" step="1">' +
                '</div>' +
                '<div class="col-action">' +
                    '<button type="button" class="action remove-row" title="' + $t('Remover') + '">' +
                        '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>' +
                    '</button>' +
                '</div>' +
            '</div>';

            $(this.options.itemsContainer).append(template);
            this.rowIndex++;
        },

        _removeRow: function ($row) {
            var rows = $(this.options.itemsContainer).find('.item-row');
            if (rows.length > 1) {
                $row.fadeOut(200, function () {
                    $(this).remove();
                });
            } else {
                // Clear the last row instead of removing
                $row.find('input').val('');
                $row.find('.qty-input').val('1');
            }
        },

        _parseBulkInput: function () {
            var self = this;
            var text = $(this.options.bulkInput).val().trim();

            if (!text) {
                this._showResults($t('Nenhum texto para processar.'), true);
                return;
            }

            var lines = text.split(/[\r\n]+/);
            var items = [];
            var errors = [];

            lines.forEach(function (line, index) {
                line = line.trim();
                if (!line) return;

                // Parse line: SKU, QTY or SKU;QTY or SKU QTY
                var parts = line.split(/[,;\t\s]+/);
                var sku = parts[0] ? parts[0].trim() : '';
                var qty = parts[1] ? parseInt(parts[1].trim(), 10) : 1;

                if (sku) {
                    if (qty <= 0 || isNaN(qty)) qty = 1;
                    items.push({ sku: sku, qty: qty, line: index + 1 });
                } else {
                    errors.push($t('Linha %1: SKU inválido').replace('%1', index + 1));
                }
            });

            if (items.length === 0) {
                this._showResults($t('Nenhum item válido encontrado.'), true);
                return;
            }

            // Add items to the form
            items.forEach(function (item) {
                self._addRowWithData(item.sku, item.qty);
            });

            var message = $t('%1 item(s) adicionado(s) ao formulário.').replace('%1', items.length);
            if (errors.length) {
                message += '<br><span class="error">' + errors.join('<br>') + '</span>';
            }
            this._showResults(message, false);

            // Clear bulk input
            $(this.options.bulkInput).val('');
        },

        _addRowWithData: function (sku, qty) {
            this._addRow();
            var $lastRow = $(this.options.itemsContainer).find('.item-row:last');
            $lastRow.find('.sku-input').val(sku);
            $lastRow.find('.qty-input').val(qty);
        },

        _submitForm: function () {
            var self = this;
            var $form = $(this.options.formSelector);
            var $submitBtn = $form.find('.action.primary.submit');

            // Validate that at least one SKU is provided
            var hasItems = false;
            $form.find('.sku-input').each(function () {
                if ($(this).val().trim()) {
                    hasItems = true;
                    return false;
                }
            });

            var bulkInput = $(this.options.bulkInput).val().trim();
            if (!hasItems && !bulkInput) {
                this._showResults($t('Por favor, adicione pelo menos um SKU.'), true);
                return;
            }

            // Show loading state
            $form.addClass('loading');
            $submitBtn.prop('disabled', true);

            $.ajax({
                url: $form.attr('action'),
                type: 'POST',
                data: $form.serialize(),
                dataType: 'json',
                success: function (response) {
                    $form.removeClass('loading');
                    $submitBtn.prop('disabled', false);

                    if (response.success) {
                        var html = '<div class="result-summary success">' +
                            '<strong>' + response.message + '</strong></div>';

                        if (response.added_products && response.added_products.length) {
                            html += '<div class="result-items">';
                            response.added_products.forEach(function (item) {
                                html += '<div class="result-item success">✓ ' + 
                                    item.sku + ' - ' + item.name + ' (Qtd: ' + item.qty + ')</div>';
                            });
                            html += '</div>';
                        }

                        if (response.errors && response.errors.length) {
                            response.errors.forEach(function (error) {
                                html += '<div class="result-item error">✗ ' + 
                                    error.sku + ': ' + error.message + '</div>';
                            });
                        }

                        html += '<div class="result-actions" style="margin-top:15px;">' +
                            '<a href="' + response.cart_url + '" class="action primary">' + 
                            $t('Ver Carrinho') + '</a></div>';

                        self._showResults(html, false);
                        self._clearForm();
                    } else {
                        self._showResults(response.message || $t('Erro ao processar.'), true);
                    }
                },
                error: function () {
                    $form.removeClass('loading');
                    $submitBtn.prop('disabled', false);
                    self._showResults($t('Erro de conexão. Tente novamente.'), true);
                }
            });
        },

        _clearForm: function () {
            var self = this;
            $(this.options.itemsContainer).find('.item-row').each(function (index) {
                if (index === 0) {
                    $(this).find('input').val('');
                    $(this).find('.qty-input').val('1');
                } else {
                    $(this).remove();
                }
            });
            $(this.options.bulkInput).val('');
        },

        _showResults: function (html, isError) {
            var $section = $(this.options.resultsSection);
            var $content = $(this.options.resultsContent);

            $section.removeClass('error');
            if (isError) {
                $section.addClass('error');
            }

            $content.html(html);
            $section.slideDown(200);

            // Scroll to results
            $('html, body').animate({
                scrollTop: $section.offset().top - 100
            }, 300);
        },

        _hideResults: function () {
            $(this.options.resultsSection).slideUp(200);
        }
    });

    return $.grupoawamotos.quickOrder;
});
