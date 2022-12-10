<?php

declare(strict_types=1);

namespace App\Service\Inbox;

use App\Entity\Account;
use App\JsonArray;
use App\Service\AccountService;
use App\Service\StatusService;

class Create implements TypeProcessorInterface
{
    protected AccountService $accountService;
    protected StatusService $statusService;

    public function __construct(AccountService $accountService, StatusService $statusService)
    {
        $this->accountService = $accountService;
        $this->statusService = $statusService;
    }

    public function process(Account $source, JsonArray $message): bool
    {
        $object = $message->getJsonArrayOrNull('[object]');
        if ($object === null) {
            return false;
        }

        $type = $object->getString('[type]', '');
        switch ($type) {
            case 'Note':
                return $this->processMessage($source, $object);
            case 'Question':
                return $this->processMessage($source, $object);
            default:
                throw new \Exception('Unknown object type: ' . $type);
        }
    }

    public function canProcess(string $type): bool
    {
        return $type === 'create';
    }

    /**
     * @param JsonArray $object
     * @throws \Exception
     */
    protected function processMessage(Account $source, JsonArray $object): bool
    {
        // @TODO: We probably need to check for forwarded messages first

        // We've already seen this status
        $status = $this->statusService->findStatusByURI($object->getString('[id]', ''));
        if ($status) {
            return false;
        }

        $status = $this->statusService->createStatusFromObject($source, $object);

        return $status !== null;
    }
}
