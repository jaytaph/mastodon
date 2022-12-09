<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Account;

class MessageService
{
    /**
     * Converts JSON-LD to a canonical form
     *
     * @param array<string,string|string[]> $data
     * @return string
     * @throws \JsonException
     */
    public function canonicalize(array $data): string
    {
        try {
            $ret = jsonld_normalize(
                $this->array2object($data),
                [
                    'algorithm' => 'URDNA2015',
                    'format' => 'application/nquads',
                ]
            );
        } catch (\Throwable $e) {
            throw new \RuntimeException('Cannot canonicalize message', 0, $e);
        }

        return strval($ret);
    }

    protected function hash(string $data): string
    {
        return hash('sha256', $data);
    }

    /**
     * @param array<string,string|string[]> $message
     */
    public function hasSignature(array $message): bool
    {
        return isset($message['signature']);
    }

    /**
     * Validates a message that has been created by 'creator'
     *
     * @param Account $creator*
     * @param array<string,string|string[]> $message
     * @return bool
     * @throws \JsonException
     */
    public function validate(Account $creator, array $message): bool
    {
        if (! isset($message['signature'])) {
            return false;
        }

        /** @var array<string|string[]> $signature */
        $signature = $message['signature'];

        /** @var string $signatureValue */
        $signatureValue = $signature['signatureValue'];
        unset($message['signature']);

        if ($signature['type'] != 'RsaSignature2017') {
            return false;
        }

        // Unset the things that are not signed
        unset($signature['type']);
        unset($signature['id']);
        unset($signature['signatureValue']);
        $signature['@context'] = 'https://w3id.org/identity/v1';

        // Create the hash of both the message and the signature
        $messageHash = $this->hash($this->canonicalize($signature)) . $this->hash($this->canonicalize($message));
        $ret = openssl_verify($messageHash, base64_decode($signatureValue), $creator->getPublicKeyPem() ?? '', OPENSSL_ALGO_SHA256);
        return $ret == 1;
    }

    public function createHashDigest(string $message): string
    {
        // Hashing the message does not require canonicalization. We just need to make sure we send the message as-is within the HTTP POST body
        return "SHA-256=" . base64_encode(hash('sha256', $message, true));
    }

    /**
     * @param array<string,string|string[]> $data
     */
    protected function array2object(array $data): mixed
    {
        $json = json_encode($data);
        if (!$json) {
            $json = '';
        }

        return json_decode($json, false, 512, JSON_THROW_ON_ERROR);
    }
}
