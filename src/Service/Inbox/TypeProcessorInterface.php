<?php

declare(strict_types=1);

namespace App\Service\Inbox;

use App\Entity\Account;

interface TypeProcessorInterface
{
    public function process(Account $source, array $message): bool;
    public function canProcess(string $type): bool;
}
