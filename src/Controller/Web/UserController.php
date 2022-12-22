<?php

declare(strict_types=1);

namespace App\Controller\Web;

use App\Controller\AccountTrait;
use App\Service\AccountService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    use AccountTrait;

    protected AccountService $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
    }

    #[Cache(vary: ['Accept'])]
    #[Route('/@{acct}', name: 'app_users_show_profile')]
    public function profile(Request $request, string $acct): Response
    {
        $account = $this->findAccount($acct, localOnly: true);
        if (!$account) {
            throw $this->createNotFoundException();
        }

        if ($request->getPreferredFormat() == 'json') {
            return new JsonResponse($this->accountService->toProfileJson($account));
        }

        return $this->render('user/show.html.twig', [
            'account' => $account,
        ]);
    }

    #[Cache(vary: ['Accept'])]
    #[Route('/users/{acct}', name: 'app_users_show')]
    public function user(Request $request, string $acct): Response
    {
        $account = $this->findAccount($acct, localOnly: true);
        if (!$account) {
            throw $this->createNotFoundException();
        }

        if ($request->getPreferredFormat() == 'json') {
            return new JsonResponse($this->accountService->toProfileJson($account));
        }

        return $this->render('user/show.html.twig', [
            'account' => $account,
        ]);
    }
}
