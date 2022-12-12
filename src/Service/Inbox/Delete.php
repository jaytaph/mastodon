<?php

declare(strict_types=1);

namespace App\Service\Inbox;

use App\Entity\Account;
use Jaytaph\TypeArray\TypeArray;

class Delete implements TypeProcessorInterface
{
    public function process(Account $source, TypeArray $message): bool
    {
        return true;
    }

    public function canProcess(string $type): bool
    {
        return $type === 'delete';
    }
}
