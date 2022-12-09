<?php

declare(strict_types=1);

namespace App\Service\Inbox;

use App\Entity\Account;
use App\Entity\Follower;
use App\Service\AccountService;
use App\Service\AuthClientService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

class Follow implements TypeProcessorInterface
{
    protected AccountService $accountService;
    protected EntityManagerInterface $doctrine;
    protected AuthClientService $authClientService;

    public function __construct(AccountService $accountService, EntityManagerInterface $doctrine, AuthClientService $authClientService)
    {
        $this->accountService = $accountService;
        $this->doctrine = $doctrine;
        $this->authClientService = $authClientService;
    }

    public function process(Account $source, array $message): bool
    {
        // Store in the followers table
        $actor = $this->accountService->findAccount($message['actor'], true, $source);
        if (! $actor) {
            return false;
        }

        $follower = $this->doctrine->getRepository(Follower::class)->findOneBy(['user' => $source, 'follow' => $actor]);
        if (! $follower) {
            // Setup new following
            $follower = new Follower();
            $follower->setUser($source);
            $follower->setFollow($actor);
        }

        $follower->setAccepted(true);

        // Send back accept response
        $data = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => 'https://dhpt.nl/users/' . $source->getAcct() . '/' . Uuid::v4(),
            'type' => 'Accept',
            'actor' => $message['object'],
            'object' => $message,
        ];

        $result = $this->authClientService->send($source, $actor, $data);
        if ($result->getStatusCode() >= 200 && $result->getStatusCode() < 300) {
            $this->doctrine->persist($follower);
            $this->doctrine->flush();
        }

        return true;
    }

    public function canProcess(string $type): bool
    {
        return $type === 'follow';
    }
}
