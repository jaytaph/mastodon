<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Account;
use App\Service\Inbox\TypeProcessorInterface;
use Jaytaph\TypeArray\TypeArray;

class InboxService
{
    /** @var TypeProcessorInterface[] */
    protected array $processors = [];
    protected MessageService $messageService;
    protected AccountService $accountService;

    /** @param TypeProcessorInterface[] $processors */
    public function __construct(iterable $processors, MessageService $messageService, AccountService $accountService)
    {
        $processors = $processors instanceof \Traversable ? iterator_to_array($processors) : $processors;
        $this->processors = $processors;

        $this->messageService = $messageService;
        $this->accountService = $accountService;
    }

    public function processMessage(Account $source, TypeArray $message, bool $validateMessage = true): bool
    {
        if (!$message->exists('[type]')) {
            return false;
        }

        // Validate message if it has a signature
        if ($validateMessage && $this->messageService->hasSignature($message)) {
            $creator = $this->accountService->fetchMessageCreator($source, $message);
            if (!$creator) {
                return false;
            }
            if (!$this->messageService->validate($creator, $message)) {
                return false;
            }
        }

        foreach ($this->processors as $processor) {
            if ($processor->canProcess(strtolower($message->getString('[type]', '')))) {
                return $processor->process($source, $message);
            }
        }

        return false;
    }
}
