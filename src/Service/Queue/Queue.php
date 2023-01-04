<?php

declare(strict_types=1);

namespace App\Service\Queue;

use App\Entity\DbQueueEntry;
use Doctrine\ORM\EntityManagerInterface;
use Jaytaph\TypeArray\TypeArray;

class Queue implements QueueInterface
{
    /** @var WorkerInterface[] */
    protected iterable $workers;
    protected EntityManagerInterface $doctrine;

    /** @param WorkerInterface[] $workers */
    public function __construct(iterable $workers, EntityManagerInterface $doctrine)
    {
        $this->workers = $workers;
        $this->doctrine = $doctrine;
    }

    public function process(): void
    {
        // find entries that are pending

        /** @var DbQueueEntry|null $dbEntry */
        $dbEntry = $this->doctrine->getRepository(DbQueueEntry::class)->findOneBy(['status' => 'pending']);
        if (is_null($dbEntry)) {
            return;
        }

        $entry = new QueueEntry();
        $entry->setType($dbEntry->getType() ?? '');
        $entry->setData($dbEntry->getData());
        $entry->setAttempts($dbEntry->getAttempts() ?? 0);
        $entry->setFinished(true);
        $entry->setFailed(false);
        $entry->setRetry(false);

        foreach ($this->workers as $worker) {
            if (! $worker->canWork($entry)) {
                continue;
            }

            $worker->work($entry);
            if ($entry->isFailed()) {
                $dbEntry->setFailed($entry->getFailedReason());
                $dbEntry->setAttempts($dbEntry->getAttempts() + 1);
                $dbEntry->setStatus('pending');

                if ($entry->getAttempts() > 5) {
                    $dbEntry->setStatus('failed');
                }
            } elseif ($entry->isFinished()) {
                $dbEntry->setStatus('finished');
            }

            $this->doctrine->persist($dbEntry);
            $this->doctrine->flush();
        }
    }

    public function queue(string $type, TypeArray $data): bool
    {
        $entry = new DbQueueEntry();
        $entry->setCreatedAt(new \DateTimeImmutable());
        $entry->setLastRunAt(new \DateTimeImmutable());
        $entry->setType($type);
        $entry->setData($data);
        $entry->setAttempts(0);
        $entry->setFailed('');
        $entry->setStatus('pending');
        $this->doctrine->persist($entry);
        $this->doctrine->flush();

        return true;
    }
}
