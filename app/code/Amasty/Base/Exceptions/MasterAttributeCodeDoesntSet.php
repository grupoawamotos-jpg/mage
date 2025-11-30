<?php
namespace Amasty\Base\Exceptions;

class MasterAttributeCodeDoesntSet extends \Magento\Framework\Exception\LocalizedException
{
    public function __construct(?\Magento\Framework\Phrase $phrase = null, ?\Throwable $cause = null, int $code = 0)
    {
        if (!$phrase) {
            $phrase = __('Master Attribute Code doesn\'t set.');
        }
        parent::__construct($phrase, $cause, $code);
    }
}
