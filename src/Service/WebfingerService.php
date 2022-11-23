<?php

declare(strict_types=1);

namespace App\Service;

use App\Config;
use App\Entity\Account;
use GuzzleHttp\Client;

class WebfingerService
{
    protected AccountService $accountService;
    protected AuthClientService $authClientService;

    public function __construct(AccountService $accountService, AuthClientService $authClientService)
    {
        $this->accountService = $accountService;
        $this->authClientService = $authClientService;
    }

    public function fetch(string $name): ?Account
    {
        $user = substr($name, 0, strpos($name, '@'));
        $domain = substr($name, strpos($name, '@') + 1);

        $client = new Client();
        $response = $client->get($domain . "/.well-known/webfinger?resource=acct:" . $user . "@" . $domain, [
            'headers' => [
                'Accept' => 'application/json'
            ]
        ]);
        $info = json_decode($response->getBody()->getContents(), true);

        dump($info);

        foreach ($info['links'] as $link) {
            if ($link['rel'] == 'self') {
                return $this->accountService->fetchRemoteAccount($link['href']);
            }
        }

        return null;
    }
}
