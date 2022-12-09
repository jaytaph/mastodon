<?php

declare(strict_types=1);

namespace App\Service\Inbox;

use App\Entity\Account;

class Reject implements TypeProcessorInterface
{
    public function process(Account $source, array $message): bool
    {
        // Reject is the message when we don't accept a follow request
        return true;
    }

    public function canProcess(string $type): bool
    {
        return $type === 'reject';
    }
}
