<?php
namespace Amasty\Base\Exceptions;

class EntityTypeCodeNotSet extends \Magento\Framework\Exception\LocalizedException
{
    public function __construct(?\Magento\Framework\Phrase $phrase = null, ?\Throwable $cause = null, int $code = 0)
    {
        if (!$phrase) {
            $phrase = __('Entity Type Code doesn\'t set.');
        }
        parent::__construct($phrase, $cause, $code);
    }
}
