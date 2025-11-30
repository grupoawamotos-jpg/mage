<?php
namespace Amasty\Base\Exceptions;

class MappingColumnDoesntExist extends \Magento\Framework\Exception\LocalizedException
{
    public function __construct(?\Magento\Framework\Phrase $phrase = null, ?\Throwable $cause = null, int $code = 0)
    {
        if (!$phrase) {
            $phrase = __('Mapping column doesn\'t exist.');
        }
        parent::__construct($phrase, $cause, $code);
    }
}
