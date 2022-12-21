<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\AccountService;
use App\Service\ConfigService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

class WebfingerController extends AbstractController
{
    protected AccountService $accountService;
    protected ConfigService $configService;

    /**
     * @param AccountService $accountService
     */
    public function __construct(AccountService $accountService, ConfigService $configService)
    {
        $this->accountService = $accountService;
        $this->configService = $configService;
    }

    #[Route('/.well-known/webfinger', name: 'app_webfinger')]
    public function webfinger(Request $request): Response
    {
        $resource = strval($request->query->get('resource'));
        if (! str_starts_with($resource, 'acct:')) {
            throw new BadRequestHttpException('Invalid resource');
        }
        $resource = str_replace('acct:', '', $resource);

        if (!str_contains($resource, '@')) {
            throw new BadRequestHttpException('Invalid resource');
        }
        [$username, $domain] = explode('@', $resource);
        if ($domain != $this->configService->getConfig()->getInstanceDomain()) {
            throw new BadRequestHttpException('Invalid resource');
        }

        $account = $this->accountService->findAccount($username);
        if (!$account) {
            throw $this->createNotFoundException();
        }

        $data = [
            'subject' => 'acct:' . $account->getUsername() . '@' . $this->configService->getConfig()->getInstanceDomain(),
            "aliases" => [
                $this->configService->getConfig()->getSiteUrl() . '/@' . $account->getUsername(),
                $this->configService->getConfig()->getSiteUrl() . "/users/" . $account->getUsername(),
            ],
            'links' => [
                [
                    "rel" => "https://webfinger.net/rel/profile-page",
                    "type" => "text/html",
                    "href" => $this->configService->getConfig()->getSiteUrl() . '/@' . $account->getUsername()
                ],
                [
                    'rel' => 'self',
                    'type' => 'application/activity+json',
                    'href' => $this->configService->getConfig()->getSiteUrl() . '/users/' . $account->getUsername(),
                ],
                [
                    "rel" => "https://ostatus.org/schema/1.0/subscribe",
                    "template" => $this->configService->getConfig()->getSiteUrl() . '/@' . $account->getUsername() . "/follow?uri={uri}"
                ]
            ],
        ];

        $response = new JsonResponse($data);
        $response->headers->set('Content-Type', 'application/jrd+json');

        return $response;
    }
}
