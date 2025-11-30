require(['jquery'], function ($) {
    'use strict';

    function loadMedia($media) {
        var source = $media.data('src');

        if (!source) {
            return;
        }

        if ($media.is('img')) {
            $media.attr('src', source);
        } else if ($media.is('video')) {
            $media.attr('src', source);

            if ($media.prop('autoplay')) {
                var element = $media.get(0);

                if (element && element.play) {
                    element.play().catch(function () {
                        /* Ignore autoplay blocks */
                    });
                }
            }
        }

        $media.removeAttr('data-src').addClass('is-loaded');
    }

    function hydrateHomeMedia() {
        var supportsObserver = typeof window.IntersectionObserver !== 'undefined';
        var observer = null;

        if (supportsObserver) {
            observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        loadMedia($(entry.target));
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                rootMargin: '250px 0px',
                threshold: 0.1
            });
        }

        $('[data-home-media], .home-media[data-src]').each(function () {
            var $media = $(this);

            if (observer) {
                observer.observe(this);
            } else {
                loadMedia($media);
            }
        });
    }

    $(function () {
        hydrateHomeMedia();
    });
});
