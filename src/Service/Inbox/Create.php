<?php

declare(strict_types=1);

namespace App\Service\Inbox;

use App\Entity\Account;
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

    /**
     * @param Account $source
     * @param mixed[] $message
     * @return bool
     */
    public function process(Account $source, array $message): bool
    {
        if (! isset($message['object'])) {
            return false;
        }

        /** @var array<string,string|string[]> $object */
        $object = $message['object'];

        switch ($object['type']) {
            case 'Note':
                return $this->processMessage($source, $object);
            case 'Question':
                return $this->processMessage($source, $object);
            default:
                throw new \Exception('Unknown object type: ' . strval($object['type']));
        }
    }

    public function canProcess(string $type): bool
    {
        return $type === 'create';
    }

    /**
     * @param array<string,string|string[]> $object
     * @throws \Exception
     */
    protected function processMessage(Account $source, array $object): bool
    {
        // @TODO: We probably need to check for forwarded messages first

        // We've already seen this status
        /** @phpstan-ignore-next-line */
        $status = $this->statusService->findStatusByURI($object['id']);
        if ($status) {
            return false;
        }

        $status = $this->statusService->createStatusFromObject($source, $object);

        return $status !== null;
    }
}
