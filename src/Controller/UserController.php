<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\AccountService;
use App\Service\InboxService;
use App\Service\ConfigService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Jaytaph\TypeArray\TypeArray;

class UserController extends AbstractController
{
    use AccountTrait;

    protected AccountService $accountService;
    protected InboxService $inboxService;

    public function __construct(AccountService $accountService, InboxService $inboxService)
    {
        $this->accountService = $accountService;
        $this->inboxService = $inboxService;
    }

    #[Route('/users/{acct}/inbox', name: 'app_users_inbox')]
    public function inbox(string $acct, Request $request): Response
    {
        $account = $this->findAccount($acct, localOnly: true);
        if (!$account) {
            throw $this->createNotFoundException();
        }

        // For now, we store all contents in a file for later processing...
        // @TODO: Path injection
        file_put_contents("../var/uploads/$acct-inbox.txt", $request->getContent() . "\n", FILE_APPEND);

        $message = TypeArray::fromJson($request->getContent());
        if (! $message->isEmpty()) {
            $this->inboxService->processMessage($account, $message);
        }

        return new Response("donkey");
    }

    #[Route('/users/{acct}', name: 'app_users_show')]
    public function user(string $acct, ConfigService $configService): Response
    {
        $account = $this->findAccount($acct, localOnly: true);
        if (!$account) {
            throw $this->createNotFoundException();
        }

        $accountUrl = $configService->getConfig()->getSiteUrl() . '/users/' . $account->getUsername();

        $data = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => $accountUrl,
            'type' => 'Person',
            'preferredUsername' => $account->getDisplayName(),
            'name' => $account->getUsername(),
            'summary' => $account->getNote(),
            'inbox' => $accountUrl . '/inbox',
            'outbox' => $accountUrl . '/outbox',
            'publicKey' => [
                'id' => $accountUrl . '#main-key',
                'owner' => $accountUrl,
                'publicKeyPem' => $account->getPublicKeyPem(),
            ],
            'followers' => $accountUrl . '/followers',
            'following' => $accountUrl . '/following',
        ];

        $response = new JsonResponse($data);
        $response->headers->set('Content-Type', 'application/activity+json');

        return $response;
    }
}
