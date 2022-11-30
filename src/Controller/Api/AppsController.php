<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\BaseApiController;
use League\Bundle\OAuth2ServerBundle\Manager\ClientManagerInterface;
use League\Bundle\OAuth2ServerBundle\Model\Client;
use League\Bundle\OAuth2ServerBundle\ValueObject\Grant;
use League\Bundle\OAuth2ServerBundle\ValueObject\RedirectUri;
use League\Bundle\OAuth2ServerBundle\ValueObject\Scope;
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
    public function apps(ClientManagerInterface $clientManager, Request $request): Response
    {
        $id = bin2hex(random_bytes(16));
        $secret = 'dhpt_secret_' . bin2hex(random_bytes(32));

        $client = new Client(strval($request->get('client_name')), $id, $secret);
        $client->setActive(true);

        $scopes = explode(' ', strval($request->get('scopes')));
        $grants = ['authorization_code', 'refresh_token'];

        $client
            ->setRedirectUris(...array_map(static function (string $redirectUri): RedirectUri {
                try {
                    return new RedirectUri($redirectUri);
                } catch (\Throwable) {
                    // @TODO: Handle invalid redirect URI
                    return new RedirectUri('https://localhost');
                }
            }, explode(' ', strval($request->get('redirect_uris')))));
        $client
            ->setGrants(...array_map(static function (string $grant): Grant {
                return new Grant($grant);
            }, $grants));
        $client
            ->setScopes(...array_map(static function (string $scope): Scope {
                return new Scope($scope);
            }, $scopes))
        ;

        $clientManager->save($client);

        $data = [
            'name' => $client->getName(),
            'client_id' => $client->getIdentifier(),
            'client_secret' => $client->getSecret(),
        ];

        return new JsonResponse($data);
    }
}
