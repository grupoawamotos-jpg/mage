<?php
namespace Amasty\Base\Exceptions;

class WrongValidatorInterface extends \Magento\Framework\Exception\LocalizedException
{
    public function __construct(?\Magento\Framework\Phrase $phrase = null, ?\Throwable $cause = null, int $code = 0)
    {
        if (!$phrase) {
            $phrase = __('Wrong Validator Interface.');
        }
        parent::__construct($phrase, $cause, $code);
    }
}
