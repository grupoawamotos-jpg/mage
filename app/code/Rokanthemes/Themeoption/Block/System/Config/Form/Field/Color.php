<?php
namespace Rokanthemes\Themeoption\Block\System\Config\Form\Field;

use Magento\Framework\Registry;

class Color extends \Magento\Config\Block\System\Config\Form\Field
{
    protected $_coreRegistry;
    
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        Registry $coreRegistry,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }
    
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $base = $this->getBaseUrl();
        $cpPath = $base . 'pub/media/js/';

        $element->setClass(trim($element->getClass() . ' jscolor'));

        $html = $element->getElementHtml();

        if (!$this->_coreRegistry->registry('colorpicker_loaded')) {
            $styles = '<style type="text/css">input.jscolor { background-image: url(' . $cpPath . 'color.png) !important; background-position: calc(100% - 8px) center; background-repeat: no-repeat; padding-right: 44px !important; } input.jscolor.disabled,input.jscolor[disabled] { pointer-events: none; }</style>';
            $init = '<script type="text/x-magento-init">' . json_encode([
                '*' => [
                    'Rokanthemes_Themeoption/js/color-picker' => [
                        'src' => $cpPath . 'jscolor.js',
                        'selector' => '.jscolor'
                    ]
                ]
            ], JSON_UNESCAPED_SLASHES) . '</script>';

            $html .= $styles . $init;
            $this->_coreRegistry->register('colorpicker_loaded', 1);
        }

        return $html;
    }
}