<?php

declare(strict_types=1);

namespace App\Service\Inbox;

use App\Entity\Account;
use App\Entity\Follower;
use App\Service\AccountService;
use App\Service\MessageService;
use Doctrine\ORM\EntityManagerInterface;

class Accept implements TypeProcessorInterface
{
    protected MessageService $messageService;
    protected AccountService $accountService;
    protected EntityManagerInterface $doctrine;

    public function __construct(MessageService $messageService, AccountService $accountService, EntityManagerInterface $doctrine)
    {
        $this->messageService = $messageService;
        $this->accountService = $accountService;
        $this->doctrine = $doctrine;
    }

    public function process(Account $source, array $message): bool
    {
        $actor = $this->accountService->findAccount($message['actor'], true, $source);
        if (! $actor) {
            return false;
        }

        $follower = $this->doctrine->getRepository(Follower::class)->findOneBy(['user' => $actor, 'follow' => $source]);
        if (! $follower) {
            // Setup new following
            $follower = new Follower();
            $follower->setUser($actor);
            $follower->setFollow($source);
        }

        $follower->setAccepted(true);

        $this->doctrine->persist($follower);
        $this->doctrine->flush();

        return true;
    }

    public function canProcess(string $type): bool {
        return $type === 'accept';
    }
}
