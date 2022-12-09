<?php

declare(strict_types=1);

namespace App\Service\Inbox;

use App\Entity\Account;
use App\Service\PollService;
use App\Service\StatusService;

class Update implements TypeProcessorInterface
{
    protected StatusService $statusService;
    protected PollService $pollService;

    public function __construct(StatusService $statusService, PollService $pollService)
    {
        $this->statusService = $statusService;
        $this->pollService = $pollService;
    }

    /**
     * @param Account $source
     * @param array<string,string|string[]> $message
     * @return bool
     */
    public function process(Account $source, array $message): bool
    {
        /** @var array<string,string|string[]> $object */
        $object = $message['object'];
        if (!$object) {
            return false;
        }

        switch ($object['type']) {
            case 'Question':
                return $this->updateQuestion($object);
            case 'Person':
                // @TODO: update person
                return true;
            case 'Note':
                // @TODO: update note/status
                return true;
            default:
                // @TODO: don't ignore other types
        }

        return true;
    }

    public function canProcess(string $type): bool
    {
        return $type === 'update';
    }

    /**
     * @param array<string,string|string[]> $object
     * @return bool
     */
    protected function updateQuestion(array $object): bool
    {
        /** @phpstan-ignore-next-line */
        $status = $this->statusService->findStatusByUri($object['id']);
        if (!$status) {
            return false;
        }

        if ($status->getPoll() == null) {
            return false;
        }

        $this->pollService->updatePoll($status->getPoll(), $object);

        return true;
    }
}
