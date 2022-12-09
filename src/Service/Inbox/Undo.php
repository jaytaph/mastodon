<?php

declare(strict_types=1);

namespace App\Service\Inbox;

use App\Entity\Account;

class Undo implements TypeProcessorInterface
{
    /**
     * @param Account $source
     * @param mixed[] $message
     * @return bool
     */
    public function process(Account $source, array $message): bool
    {
        return true;
    }

    public function canProcess(string $type): bool
    {
        return $type === 'undo';
    }
}
