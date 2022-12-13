<?php

declare(strict_types=1);

namespace App\Service\Inbox;

use App\Entity\Account;
use Jaytaph\TypeArray\TypeArray;

interface TypeProcessorInterface
{
    public function process(Account $source, TypeArray $message): bool;
    public function canProcess(string $type): bool;
}
