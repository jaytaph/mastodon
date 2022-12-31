<?php

declare(strict_types=1);

namespace App\Service\Queue;

use Jaytaph\TypeArray\TypeArray;

interface QueueInterface
{
    public function queue(string $type, TypeArray $data): bool;
}
