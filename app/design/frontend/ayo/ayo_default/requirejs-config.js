/**
 * Ayo Theme - RequireJS Configuration
 */
var config = {
    map: {
        '*': {
            // Form Widgets
            floatingLabels: 'js/floating-labels-widget',
            passwordStrength: 'js/password-strength-widget',
            charCounter: 'js/char-counter-widget',
            formValidationStyles: 'js/form-validation-styles-widget',
            accessibility: 'js/accessibility-widget',
            placeholders: 'js/placeholders-widget',
            
            // Theme Components
            headerA11y: 'js/header-a11y',
            lazyProductImages: 'js/lazy-product-images',
            brasilMasks: 'js/brasil-masks',
            footerCustom: 'js/footer-custom',
            
            // Rokanthemes Core
            'rokanthemes/owl': 'Rokanthemes_RokanBase/js/owl_carousel',
            'rokanthemes/fancybox': 'Rokanthemes_RokanBase/js/jquery_fancybox',
            'rokanthemes/elevatezoom': 'Rokanthemes_RokanBase/js/jquery.elevatezoom',
            'rokanthemes/choose': 'Rokanthemes_RokanBase/js/jquery_choose',
            'rokanthemes/equalheight': 'Rokanthemes_RokanBase/js/equalheight',
            'rokanthemes/lazyloadimg': 'Rokanthemes_RokanBase/js/jquery.lazyload.min',
            'rokanthemes/bxslider': 'Rokanthemes_Themeoption/js/jquery.bxslider.min',
            'rokanthemes/customsrollbar': 'Rokanthemes_Themeoption/js/jquery.mCustomScrollbar.concat.min',
            'rokanthemes/hoverdir': 'Rokanthemes_Themeoption/js/jquery.hoverdir',
            'rokanthemes/timecircles': 'Rokanthemes_Superdeals/js/timecircles',
            'bootstrap': 'Rokanthemes_Brand/js/bootstrap.min',
            'productQuickview': 'Rokanthemes_QuickView/js/quickview',
            'quickview/bxslider': 'Rokanthemes_QuickView/js/jquery.bxslider',
            'quickview/cloudzoom': 'Rokanthemes_QuickView/js/cloud-zoom'
        }
    },
    deps: [
        'js/theme',
        'js/responsive',
        'js/lazy-product-images',
        'js/header-a11y',
        'js/brasil-masks'
    ],
    shim: {
        'js/brasil-masks': ['jquery'],
        'js/header-a11y': ['jquery'],
        'js/lazy-product-images': ['jquery'],
        'js/footer-custom': ['jquery', 'rokanthemes/owl'],
        'rokanthemes/owl': ['jquery'],
        'rokanthemes/elevatezoom': ['jquery'],
        'rokanthemes/choose': ['jquery'],
        'rokanthemes/fancybox': ['jquery'],
        'rokanthemes/lazyloadimg': ['jquery'],
        'rokanthemes/bxslider': ['jquery'],
        'rokanthemes/customsrollbar': ['jquery'],
        'rokanthemes/hoverdir': ['jquery'],
        'rokanthemes/timecircles': ['jquery'],
        'bootstrap': ['jquery'],
        'quickview/bxslider': ['jquery'],
        'quickview/cloudzoom': ['jquery']
    }
};
