<?php

declare(strict_types=1);

namespace App\Service;

use League\Bundle\OAuth2ServerBundle\Manager\ClientManagerInterface;
use League\Bundle\OAuth2ServerBundle\Model\Client;
use League\Bundle\OAuth2ServerBundle\ValueObject\Grant;
use League\Bundle\OAuth2ServerBundle\ValueObject\RedirectUri;
use League\Bundle\OAuth2ServerBundle\ValueObject\Scope;

class OauthService
{
    protected ClientManagerInterface $clientManager;

    public function __construct(ClientManagerInterface $clientManager)
    {
        $this->clientManager = $clientManager;
    }

    /**
     * @return string[]
     * @throws \Exception
     */
    public function createClient(string $clientName, string $scopes, string $redirectUris): array
    {
        $id = bin2hex(random_bytes(16));
        $secret = 'dhpt_secret_' . bin2hex(random_bytes(32));

        $client = new Client($clientName, $id, $secret);
        $client->setActive(true);

        $scopes = explode(' ', $scopes);
        $grants = ['authorization_code', 'refresh_token'];

        $client
            ->setRedirectUris(...array_map(static function (string $redirectUri): RedirectUri {
                try {
                    return new RedirectUri($redirectUri);
                } catch (\Throwable) {
                    // @TODO: Handle invalid redirect URI
                    return new RedirectUri('https://localhost');
                }
            }, explode(' ', $redirectUris)));
        $client
            ->setGrants(...array_map(static function (string $grant): Grant {
                return new Grant($grant);
            }, $grants));
        $client
            ->setScopes(...array_map(static function (string $scope): Scope {
                return new Scope($scope);
            }, $scopes))
        ;

        $this->clientManager->save($client);

        return [
            'name' => $client->getName(),
            'client_id' => $client->getIdentifier(),
            'client_secret' => $client->getSecret() ?? '',
        ];
    }
}
