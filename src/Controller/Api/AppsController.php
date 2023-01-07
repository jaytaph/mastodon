<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\BaseApiController;
use App\Service\OauthService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AppsController extends BaseApiController
{
    /**
     * @throws \Exception
     */
    #[Route('/api/v1/apps', name: 'api_apps', methods: ['POST'])]
    #[IsGranted('PUBLIC_ACCESS')]
    public function apps(OauthService $oauthService, Request $request): Response
    {
        $client = $oauthService->createClient(
            clientName: strval($request->get('name')),
            scopes: strval($request->get('scopes')),
            redirectUris: strval($request->get('redirect_uris')),
        );

        return new JsonResponse($client);
    }
}
