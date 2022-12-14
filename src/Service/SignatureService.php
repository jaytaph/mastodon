<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Account;
use Jaytaph\TypeArray\TypeArray;

class SignatureService
{
    public function hasSignature(TypeArray $message): bool
    {
        return $message->exists('[signature]');
    }

    /**
     * Validates a message that has been created by 'creator'
     */
    public function validate(Account $creator, TypeArray $message): bool
    {
        $signature = $message->getTypeArrayOrNull('[signature]');
        if ($signature === null) {
            return false;
        }

        if ($signature->getString('[type]', '') !== 'RsaSignature2017') {
            return false;
        }

        $messageArr = $message->toArray();
        unset($messageArr['signature']);

        // Unset the things that are not signed
        $sigArr = $signature->toArray();
        unset($sigArr['type']);
        unset($sigArr['id']);
        unset($sigArr['signatureValue']);
        $sigArr['@context'] = 'https://w3id.org/identity/v1';

        // Create the hash of both the message and the signature
        $messageHash = $this->hash($this->canonicalize(new TypeArray($sigArr))) . $this->hash($this->canonicalize(new TypeArray($messageArr)));

        $signatureValue = $signature->getString('[signatureValue]', '');
        $ret = openssl_verify($messageHash, base64_decode($signatureValue), $creator->getPublicKeyPem() ?? '', OPENSSL_ALGO_SHA256);

        return $ret == 1;
    }

    // Generates a signature for a given message
    public function sign(Account $creator, TypeArray $message): TypeArray
    {
        $sigArr = [
            'created' => (new \DateTime("now", new \DateTimeZone('GMT')))->format('Y-m-d\TH:i:s\Z'),
            'creator' => $creator->getUri() . '#main-key',
        ];

        $messageHash = $this->hash($this->canonicalize(new TypeArray($sigArr))) . $this->hash($this->canonicalize($message));

        $ret = openssl_sign($messageHash, $signatureValue, $creator->getPrivateKeyPem() ?? '', OPENSSL_ALGO_SHA256);
        if ($ret) {
            return TypeArray::empty();
        }

        $signatureValue = base64_encode($signatureValue);
        $sigArr['signatureValue'] = $signatureValue;
        $sigArr['type'] = 'RsaSignature2017';
        $sigArr['id'] = $creator->getUri() . '#signature-' . $sigArr['created'];

        return new TypeArray($sigArr);
    }

    public function createHashDigest(string $message): string
    {
        // Hashing the message does not require canonicalization. We just need to make sure we send the message as-is within the HTTP POST body
        return "SHA-256=" . base64_encode(hash('sha256', $message, true));
    }

    /**
     * Converts JSON-LD to a canonical form
     */
    protected function canonicalize(TypeArray $data): string
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

    protected function array2object(TypeArray $data): mixed
    {
        $json = json_encode($data->toArray());
        if (!$json) {
            $json = '';
        }

        return json_decode($json, false, 512, JSON_THROW_ON_ERROR);
    }
}
