<?php

declare(strict_types=1);

namespace App\Service\Queue;

interface WorkerInterface
{
    public function canWork(QueueEntry $entry): bool;
    public function work(QueueEntry $entry): void;
}
