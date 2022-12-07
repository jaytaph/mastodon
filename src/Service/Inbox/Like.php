<?php

declare(strict_types=1);

namespace App\Service\Inbox;

use App\Entity\Account;

class Like implements TypeProcessorInterface
{
    public function process(Account $source, array $message): bool {
        // Like is the message send when we liked a certain post
        return true;
    }

    public function canProcess(string $type): bool {
        return $type === 'like';
    }
}
