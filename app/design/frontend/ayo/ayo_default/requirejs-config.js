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
        'js/responsive'
    ],
    shim: {
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
