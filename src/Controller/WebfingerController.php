<?php

declare(strict_types=1);

namespace App\Controller;

use App\Config;
use App\Service\AccountService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class WebfingerController extends AbstractController
{
    protected AccountService $accountService;

    /**
     * @param AccountService $accountService
     */
    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
    }

    #[Route('/.well-known/webfinger', name: 'app_webfinger')]
    public function webfinger(Request $request): Response
    {
        $resource = $request->query->get('resource');
        if (! str_starts_with($resource, 'acct:')) {
            throw new BadRequestHttpException('Invalid resource');
        }
        $resource = str_replace('acct:', '', $resource);

        if (!str_contains($resource, '@')) {
            throw new BadRequestHttpException('Invalid resource');
        }
        [$username, $domain] = explode('@', $resource);
        if ($domain != Config::SITE_DOMAIN) {
            throw new BadRequestHttpException('Invalid resource');
        }

        $account = $this->accountService->findAccount($username);
        if (!$account) {
            throw new NotFoundHttpException();
        }

        $data = [
            'subject' => 'acct:' . $account->getUsername() . '@' . Config::SITE_DOMAIN,
            "aliases" => [
                Config::SITE_URL . '/@' . $account->getUsername(),
                Config::SITE_URL . "/users/" . $account->getUsername(),
            ],
            'links' => [
                [
                    "rel" => "http://webfinger.net/rel/profile-page",
                    "type" => "text/html",
                    "href" => Config::SITE_URL . '/@' . $account->getUsername()
                ],
                [
                    'rel' => 'self',
                    'type' => 'application/activity+json',
                    'href' => Config::SITE_URL . '/users/' . $account->getUsername(),
                ],
                [
                    "rel" => "http://ostatus.org/schema/1.0/subscribe",
                    "template" => Config::SITE_URL . '/@' . $account->getUsername() . "/follow?uri={uri}"
                ]
            ],
        ];

        $response = new JsonResponse($data);
        $response->headers->set('Content-Type', 'application/jrd+json');

        return $response;
    }
}
