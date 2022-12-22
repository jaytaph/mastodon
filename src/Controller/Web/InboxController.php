<?php

declare(strict_types=1);

namespace App\Controller\Web;

use App\Controller\AccountTrait;
use App\Service\AccountService;
use App\Service\InboxService;
use Jaytaph\TypeArray\TypeArray;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InboxController extends AbstractController
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

        $message = TypeArray::fromJson($request->getContent());
        if (! $message->isEmpty()) {
            $this->inboxService->processMessage($account, $message, validateMessage: true);
        }

        return new Response("", Response::HTTP_OK);
    }
}
