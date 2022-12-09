<?php

declare(strict_types=1);

namespace App\Service\Inbox;

use App\Entity\Account;

interface TypeProcessorInterface
{
    /**
     * @param Account $source
     * @param mixed[] $message
     * @return bool
     */
    public function process(Account $source, array $message): bool;
    public function canProcess(string $type): bool;
}
