<?php

declare(strict_types=1);

namespace App\Controller;

use App\Config;
use App\Service\AccountService;
use App\Service\InboxService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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

        $message = json_decode($request->getContent(), true);
        if ($message) {
            /** @var array<string,string|string[]> $message */
            $this->inboxService->processMessage($account, $message);
        }

        return new Response("donkey");
    }

    #[Route('/users/{acct}', name: 'app_users_show')]
    public function user(string $acct): Response
    {
        $account = $this->findAccount($acct, localOnly: true);
        if (!$account) {
            throw $this->createNotFoundException();
        }

        $accountUrl = Config::SITE_URL . '/users/' . $account->getUsername();

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
