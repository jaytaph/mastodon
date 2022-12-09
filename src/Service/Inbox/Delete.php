<?php

declare(strict_types=1);

namespace App\Service\Inbox;

use App\Entity\Account;

class Delete implements TypeProcessorInterface
{
    public function process(Account $source, array $message): bool
    {
        return true;
    }

    public function canProcess(string $type): bool
    {
        return $type === 'delete';
    }
}
