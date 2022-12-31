<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Account;
use App\Service\Inbox\TypeProcessorInterface;
use Jaytaph\TypeArray\TypeArray;

class InboxService
{
    protected SignatureService $signatureService;
    protected AccountService $accountService;

    /** @var TypeProcessorInterface[] */
    protected array $processors = [];

    /** @param TypeProcessorInterface[] $processors */
    public function __construct(iterable $processors, SignatureService $signatureService, AccountService $accountService)
    {
        $processors = $processors instanceof \Traversable ? iterator_to_array($processors) : $processors;
        $this->processors = $processors;

        $this->signatureService = $signatureService;
        $this->accountService = $accountService;
    }

    public function processMessage(Account $source, TypeArray $message, bool $validateMessage = true): bool
    {
        if (!$message->exists('[type]')) {
            return false;
        }

        // Validate message if it has a signature
        if ($validateMessage && $this->signatureService->hasSignature($message)) {
            $creator = $this->accountService->fetchMessageCreator($source, $message);
            if (!$creator) {
                return false;
            }
            if (!$this->signatureService->validate($creator, $message)) {
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
