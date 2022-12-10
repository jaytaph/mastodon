<?php

declare(strict_types=1);

namespace App\Service\Inbox;

use App\Entity\Account;
use App\JsonArray;

class Undo implements TypeProcessorInterface
{
    public function process(Account $source, JsonArray $message): bool
    {
        return true;
    }

    public function canProcess(string $type): bool
    {
        return $type === 'undo';
    }
}
