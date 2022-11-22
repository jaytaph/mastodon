<?php

namespace App\Exception;

class SignatureValidationException extends \Exception
{
    public static function noSignature(): self
    {
        return new self('No signature found');
    }

    public static function incorrectType(): self
    {
        return new self('Incorrect signature type');
    }

    public static function accountNotFound(string $account): self
    {
        return new self("Account $account not found");
    }
}
