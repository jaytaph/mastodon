<?php

declare(strict_types=1);

namespace App\Service\Inbox;

use App\Entity\Account;
use Jaytaph\TypeArray\TypeArray;

class Reject implements TypeProcessorInterface
{
    public function process(Account $source, TypeArray $message): bool
    {
        // Reject is the message when we don't accept a follow request
        return true;
    }

    public function canProcess(string $type): bool
    {
        return $type === 'reject';
    }
}
