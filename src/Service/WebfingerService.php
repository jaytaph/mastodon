<?php

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
                return $this->fetchAccountFromWebfinger($link['href']);
            }
        }

        return null;
    }

    protected function fetchAccountFromWebfinger(string $href): ?Account
    {
        $response = $this->authClientService->fetch(Config::LOGGEDIN_USER_URL, $href);
        $data = json_decode($response->getBody()->getContents(), true);

        if (!$data || !isset($data['id'])) {
            return null;
        }

        dump($data);

        $account = new Account();
        $account->setId($data['id']);
        $account->setUsername($data['preferredUsername']);
        $account->setAcct($data['preferredUsername'] . "@" . parse_url($data['id'], PHP_URL_HOST));
        $account->setAvatar($data['icon']['url'] ?? '');
        $account->setHeader($data['image']['url'] ?? '');
        $account->setDisplayName($data['name'] ?? $data['preferredUsername']);
        $account->setLocked($data['manuallyApprovesFollowers']);
        $account->setBot($data['type'] == 'Service');
        $account->setUrl($data['url']);
        $account->setCreatedAt(new \DateTimeImmutable());
        $account->setFields($data['attachments'] ?? []);
        $account->setSource([]);
        $account->setEmojis([]);
        $account->setNote($data['summary']);

        $account->setCreatedAt(new \DateTimeImmutable($data['published'] ?? "now", new \DateTimeZone('GMT')));
        $account->setLastStatusAt(new \DateTimeImmutable("now", new \DateTimeZone('GMT')));

        $this->accountService->storeAccount($account);

        return $account;
    }
}
