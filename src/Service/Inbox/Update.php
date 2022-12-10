<?php

declare(strict_types=1);

namespace App\Service\Inbox;

use App\Entity\Account;
use App\JsonArray;
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
     * @param JsonArray $message
     * @return bool
     */
    public function process(Account $source, JsonArray $message): bool
    {
        $object = $message->getJsonArrayOrNull('[object]');
        if ($object === null) {
            return false;
        }

        switch ($object->getString('[type]', '')) {
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
     * @param JsonArray $object
     * @return bool
     */
    protected function updateQuestion(JsonArray $object): bool
    {
        $status = $this->statusService->findStatusByUri($object->getString('[id]', ''));
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
