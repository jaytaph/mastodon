<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Account;
use GuzzleHttp\Client;

class WebfingerService
{
    protected AccountService $accountService;
    protected AuthClientService $authClientService;
    protected ConfigService $configService;

    public function __construct(AccountService $accountService, AuthClientService $authClientService, ConfigService $configService)
    {
        $this->accountService = $accountService;
        $this->authClientService = $authClientService;
        $this->configService = $configService;
    }

    public function fetch(Account $source, string $name): ?Account
    {
        if (!strpos($name, '@')) {
            $name = $name . '@' . $this->configService->getConfig()->getSiteUrl();
        }
        $pos = strpos($name, '@');
        if ($pos === false) {
            return null;
        }
        $user = substr($name, 0, $pos);
        $domain = substr($name, $pos + 1);

        $client = new Client();
        $response = $client->get($domain . "/.well-known/webfinger?resource=acct:" . $user . "@" . $domain, [
            'http_errors' => false,
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        $info = json_decode($response->getBody()->getContents(), true);
        if (!is_array($info)) {
            return null;
        }

        foreach ($info['links'] ?? [] as $link) {
            if ($link['rel'] === 'self') {
                return $this->accountService->fetchRemoteAccount($source, $link['href'] ?? '');
            }
        }

        return null;
    }
}
