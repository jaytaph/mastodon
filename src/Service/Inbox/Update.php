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

    public function process(Account $source, array $message): bool {

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
                dump($object);
                dd("Cannot process update");
        }

        return true;
    }

    public function canProcess(string $type): bool {
        return $type === 'update';
    }

    protected function updateQuestion(array $object): bool
    {
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
