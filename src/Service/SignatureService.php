<?php

declare(strict_types=1);

namespace App\Service;

class SignatureService
{
    protected AccountService $accountService;
    protected WebfingerService $webfingerService;

    public function __construct(AccountService $accountService, WebfingerService $webfingerService)
    {
        $this->accountService = $accountService;
        $this->webfingerService = $webfingerService;
    }


    public function validateMessage(array $message): bool
    {
        if (! isset($message['signature'])) {
            return false;
        }

        $signature = $message['signature'];
        if ($signature['type'] != 'RsaSignature2017') {
            return false;
        }

        $creator = substr($signature['creator'], 0, strpos($signature['creator'], '#'));
        $account = $this->webfingerService->fetchAccount($creator);
        if (!$account) {
            throw new \Exception("Cannot find user $creator");
        }

        $header = [
            '@context' => 'https://w3id.org/identity/v1',
            'creator' => $signature['creator'],
            'created' => $signature['created'],
        ];


        unset($message['signature']);
        $hash = $this->hash($header) . $this->hash($message);

        dump($hash);
        return openssl_verify($hash, base64_decode($signature['signatureValue']), $account->getPublicKeyPem(), OPENSSL_ALGO_SHA256);
    }

    protected function hash(array $data): string
    {
        $json = json_encode($data, JSON_UNESCAPED_SLASHES);
        dump($json);
        return hash('sha256', $json);
    }
}
