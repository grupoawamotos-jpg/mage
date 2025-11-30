<?php
namespace Amasty\Base\Exceptions;

class NonExistentImportBehavior extends \Magento\Framework\Exception\LocalizedException
{
    public function __construct(?\Magento\Framework\Phrase $phrase = null, ?\Throwable $cause = null, int $code = 0)
    {
        if (!$phrase) {
            $phrase = __('No such Import Behavior.');
        }
        parent::__construct($phrase, $cause, $code);
    }
}
