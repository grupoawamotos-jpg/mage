<?php
namespace Amasty\Base\Exceptions;

class WrongBehaviorInterface extends \Magento\Framework\Exception\LocalizedException
{
    public function __construct(?\Magento\Framework\Phrase $phrase = null, ?\Throwable $cause = null, int $code = 0)
    {
        if (!$phrase) {
            $phrase = __('Wrong Behavior Interface.');
        }
        parent::__construct($phrase, $cause, $code);
    }
}
