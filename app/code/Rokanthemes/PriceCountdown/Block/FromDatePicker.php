<?php 
namespace Rokanthemes\PriceCountdown\Block;

use Magento\Framework\Registry;
use Magento\Backend\Block\Template\Context;

class FromDatePicker extends \Magento\Config\Block\System\Config\Form\Field 
{
	/**
     * @var  Registry
     */
    protected $_coreRegistry;
  
    /**
     * @param Context  $context
     * @param Registry $coreRegistry
     * @param array    $data
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }
  
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = $element->getElementHtml();

        $calendarConfig = [
            '#' . $element->getHtmlId() => [
                'calendar' => [
                    'showsTime' => true,
                    'timeFormat' => 'HH:mm',
                    'dateFormat' => 'mm/dd/yy'
                ]
            ]
        ];

        $html .= '<script type="text/x-magento-init">'
            . json_encode($calendarConfig, JSON_UNESCAPED_SLASHES)
            . '</script>';

        return $html;
    }
}