<?php

declare(strict_types=1);

namespace App\Service\Inbox;

use App\Entity\Account;
use App\JsonArray;

interface TypeProcessorInterface
{
    public function process(Account $source, JsonArray $message): bool;
    public function canProcess(string $type): bool;
}
