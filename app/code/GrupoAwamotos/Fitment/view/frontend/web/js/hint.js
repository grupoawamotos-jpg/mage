/**
 * GrupoAwamotos Fitment Hint JS
 * Provides autocomplete/typeahead functionality for search input
 */
define([
    'jquery',
    'underscore'
], function ($, _) {
    'use strict';

    return function (config, element) {
        var $input = $(element);
        var minChars = config.min || 3;
        var debounceDelay = config.delay || 300;
        var $hintContainer = null;

        /**
         * Initialize hint container
         */
        function initContainer() {
            if (!$hintContainer) {
                $hintContainer = $('<div class="fitment-hints"></div>');
                $hintContainer.css({
                    position: 'absolute',
                    zIndex: 1000,
                    backgroundColor: '#fff',
                    border: '1px solid #ccc',
                    borderRadius: '4px',
                    boxShadow: '0 2px 8px rgba(0,0,0,0.15)',
                    maxHeight: '200px',
                    overflowY: 'auto',
                    display: 'none',
                    width: $input.outerWidth() + 'px'
                });
                $input.after($hintContainer);
            }
        }

        /**
         * Position the hint container
         */
        function positionContainer() {
            if ($hintContainer) {
                var offset = $input.position();
                $hintContainer.css({
                    top: offset.top + $input.outerHeight() + 'px',
                    left: offset.left + 'px',
                    width: $input.outerWidth() + 'px'
                });
            }
        }

        /**
         * Show hints
         * @param {Array} hints
         */
        function showHints(hints) {
            if (!hints || !hints.length) {
                hideHints();
                return;
            }

            initContainer();
            positionContainer();
            
            $hintContainer.empty();
            
            hints.forEach(function (hint) {
                var $item = $('<div class="hint-item"></div>');
                $item.text(hint);
                $item.css({
                    padding: '8px 12px',
                    cursor: 'pointer',
                    borderBottom: '1px solid #eee'
                });
                $item.on('mouseenter', function () {
                    $(this).css('backgroundColor', '#f5f5f5');
                });
                $item.on('mouseleave', function () {
                    $(this).css('backgroundColor', '#fff');
                });
                $item.on('click', function () {
                    $input.val(hint);
                    hideHints();
                    $input.trigger('change');
                });
                $hintContainer.append($item);
            });
            
            $hintContainer.show();
        }

        /**
         * Hide hints
         */
        function hideHints() {
            if ($hintContainer) {
                $hintContainer.hide();
            }
        }

        /**
         * Fetch hints from server (placeholder - can be extended)
         * @param {string} query
         */
        var fetchHints = _.debounce(function (query) {
            // For now, just hide hints as there's no backend endpoint configured
            // This can be extended to call an API endpoint for suggestions
            hideHints();
            
            // Example implementation if endpoint exists:
            // if (config.endpoint) {
            //     $.getJSON(config.endpoint, { q: query })
            //         .done(function (data) {
            //             if (data.success && data.hints) {
            //                 showHints(data.hints);
            //             }
            //         });
            // }
        }, debounceDelay);

        // Event handlers
        $input.on('input', function () {
            var val = $(this).val();
            if (val.length >= minChars) {
                fetchHints(val);
            } else {
                hideHints();
            }
        });

        $input.on('blur', function () {
            // Delay to allow click on hint
            setTimeout(hideHints, 200);
        });

        $input.on('focus', function () {
            var val = $(this).val();
            if (val.length >= minChars) {
                fetchHints(val);
            }
        });

        // Handle window resize
        $(window).on('resize', _.debounce(positionContainer, 100));

        // Click outside to close
        $(document).on('click', function (e) {
            if (!$(e.target).closest($input).length && 
                !$(e.target).closest($hintContainer).length) {
                hideHints();
            }
        });
    };
});
