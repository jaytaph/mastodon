<?php

declare(strict_types=1);

namespace App\Controller;

use App\Config;
use App\Service\AccountService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    protected AccountService $accountService;

    /**
     * @param AccountService $accountService
     */
    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
    }

    #[Route('/users/{user}/inbox', name: 'app_users_inbox')]
    public function inbox(string $user, Request $request): Response
    {
        // For now, we store all contents in a file for later processing...
        file_put_contents("../var/uploads/{$user}-inbox.txt", $request->getCOntent() . "\n", FILE_APPEND);

        return new Response("donkey");
    }

    #[Route('/users/{acct}', name: 'app_users_show')]
    public function user(string $acct): Response
    {
        // Only local accounts are allowed
        if (str_contains($acct, '@')) {
            throw new NotFoundHttpException();
        }
        $account = $this->accountService->findAccount($acct);
        if (!$account) {
            throw new NotFoundHttpException();
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
