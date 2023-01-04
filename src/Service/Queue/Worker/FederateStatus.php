<?php

declare(strict_types=1);

namespace App\Service\Queue\Worker;

use App\Service\Queue\Queue;
use App\Service\Queue\QueueEntry;
use App\Service\Queue\WorkerInterface;
use App\Service\StatusService;
use Doctrine\ORM\EntityManagerInterface;
use Jaytaph\TypeArray\TypeArray;
use Symfony\Component\Uid\Uuid;

class FederateStatus implements WorkerInterface
{
    protected EntityManagerInterface $doctrine;
    protected StatusService $statusService;
    protected Queue $queue;

    public const TYPE = 'federate_status';

    public function __construct(Queue $queue, EntityManagerInterface $doctrine, StatusService $statusService)
    {
        $this->queue = $queue;
        $this->doctrine = $doctrine;
        $this->statusService = $statusService;
    }

    public function canWork(QueueEntry $entry): bool
    {
        return $entry->getType() === self::TYPE;
    }

    public function work(QueueEntry $entry): void
    {
        $statusId = $entry->getData()->getString('[status_id]');
        $status = $this->statusService->findStatusById(Uuid::fromString($statusId));
        if (!$status) {
            $entry->setFailedReason('Status not found');
            $entry->setFailed(true);
            return;
        }

        // find all uri's to send to
        $uris = [];
        $uris = array_merge($uris, $status->getTo());
        $uris = array_merge($uris, $status->getBto());
        $uris = array_merge($uris, $status->getCc());
        $uris = array_merge($uris, $status->getBcc());
        $uris = array_merge($uris, $status->getMentionIds());

        $uris = array_filter(array_unique($uris));
        foreach ($uris as $uri) {
            $this->queue->queue(
                SendStatus::TYPE,
                new TypeArray([
                    'uri' => $uri,
                    'status_id' => $status->getId(),
                ])
            );
        }
    }
}
