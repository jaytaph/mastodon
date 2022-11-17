<?php

namespace App\Controller;

use App\Config;
use App\Service\AccountService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    protected AccountService $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
    }


    // @TODO: THis does not have any security checks yet
    #[Route('/api/v1/accounts/verify_credentials', name: 'api_verify_credentials')]
    public function verifyCredentials(Request $request)
    {
        $account = $this->accountService->getAccount(Config::ADMIN_USER);

        return new JsonResponse($this->accountService->toJson($account));
    }

    #[Route('/api/v1/accounts/{id}', name: 'api_account')]
    public function account(string $id)
    {
        $account = $this->accountService->getAccount($id);

        return new JsonResponse($this->accountService->toJson($account));
    }

    #[Route('/api/v1/accounts/{id}/statuses', name: 'api_account_statuses')]
    public function statuses(string $id)
    {
        $data = [];

        return new JsonResponse($data);
    }

    #[Route('/api/v1/accounts/{acct}/following', name: 'api_account_following')]
    public function following(string $acct)
    {
        // Only local accounts are allowed
        if (str_contains($acct, '@')) {
            throw new NotFoundHttpException();
        }
        $account = $this->accountService->getAccount($acct);
        if (!$account) {
            throw new NotFoundHttpException();
        }

        $ret = [];
        foreach ($this->accountService->getFollowing($account) as $follower) {
            $ret[] = $this->accountService->toJson($follower);
        }

        return new JsonResponse($ret);
    }

    #[Route('/api/v1/accounts/{acct}/followers', name: 'api_account_followers')]
    public function followers(string $acct)
    {
        // Only local accounts are allowed
        if (str_contains($acct, '@')) {
            throw new NotFoundHttpException();
        }
        $account = $this->accountService->getAccount($acct);
        if (!$account) {
            throw new NotFoundHttpException();
        }


        $ret = [];
        foreach ($this->accountService->getFollowers($account) as $follower) {
            $ret[] = $this->accountService->toJson($follower);
        }

        return new JsonResponse($ret);
    }


    #[Route('/api/v1/apps', name: 'api_apps', methods: ['POST'])]
    public function apps(): Response
    {
//        return new NotFoundHttpException();

        $data = [
            'client_id' => 'DHPTID_' . bin2hex(random_bytes(32)),
            'client_secret' => 'DHPTSEC_' . bin2hex(random_bytes(32)),
        ];

        return new JsonResponse($data);
    }

    #[Route('/oauth/authorize', name: 'oauth_authorize', methods: ['GET'])]
    public function authorize(Request $request): Response
    {
        // Just approve the thing....
        $url = $request->get('redirect_uri');
        $url .= '?code=' . bin2hex(random_bytes(32));

        return new RedirectResponse($url);
    }

    #[Route('/oauth/token', name: 'oauth_token', methods: ['POST'])]
    public function token(Request $request): Response
    {
        $data = [
            'access_token' => 'DHPTACC_' . bin2hex(random_bytes(32)),
            'token_type' => 'bearer',
            'expires_in' => 3600,
            'refresh_token' => 'DHPTREF_' . bin2hex(random_bytes(32)),
        ];

        return new JsonResponse($data);
    }

    #[Route('/api/v1/instance', name: 'api_instance')]
    public function instance(): Response
    {
        $data = [
            'uri' => 'dhpt.nl',
            'title' => 'DonkeyHeads Mastodon Instance',
            'short_description' => 'Run by DonkeyHeads',
            'description' => 'Server written in PHP, so what could possibly go wrong!??',
            'email' => 'jthijssen@blafhoest.nl',
            'version' => '0.0.1',
//            'urls' => [
//                'streaming_api' => 'wss://mastodon.social'
//            ],
            'stats' => [
                'user_count' => 3463272,
                'status_count' => 56236324632,
                'domain_count' => 5235252,
            ],
            'thumbnail' => 'https://dhpt.nl/dh.jpg',
            'languages' => [
                'en'
            ],
            'registrations' => false,
            'approval_required' => true,
            'contact_account' => $this->accountService->getAccount(Config::ADMIN_USER),
        ];

        return new JsonResponse($data);
    }
}
