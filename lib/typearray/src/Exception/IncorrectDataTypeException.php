<?php

declare(strict_types=1);

namespace Jaytaph\TypeArray\Exception;

class IncorrectDataTypeException extends \Exception
{
    public function __construct(string $want, string $have)
    {
        parent::__construct(sprintf('Incorrect data type. Want: %s, have: %s', $want, $have));
    }
}
