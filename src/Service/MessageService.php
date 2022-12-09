<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Account;
use ML\JsonLD\JsonLD;
use ML\JsonLD\NQuads;

class MessageService
{
    /**
     * Converts JSON-LD to a canonical form
     *
     * @param array<string> $data
     * @return string
     * @throws \JsonException
     */
    public function canonicalize(array $data): string
    {
        // Convert JSON-LD to RDF
        $json = json_encode($data, JSON_THROW_ON_ERROR);
        $quads = JsonLD::toRdf($json, ['format' => 'application/nquads']);
        $nquads = new NQuads();
        $tmp = $nquads->serialize($quads);

        // sort the quads, as they might not be in the correct order :(
        $tmp = explode("\n", $tmp);
        sort($tmp);

        // We have blank identifiers as _:b0, _:b1, etc. We need to replace these with c14n identifiers
        $ret = "";
        foreach ($tmp as $v) {
            if (empty($v)) {
                continue;
            }
            if (str_starts_with($v, "_:b")) {
                $ret .= "_:cn14n" . substr($v, 3) . "\n";
            } else {
                $ret .= $v . "\n";
            }
        }

        return $ret;
    }

    protected function hash(string $data): string
    {
        return bin2hex(hash('sha256', $data, true));
    }

    /**
     * @param array<string> $message
     */
    public function hasSignature(array $message): bool
    {
        return isset($message['signature']);
    }

    /**
     * Validates a message that has been created by 'creator'
     *
     * @param Account $creator
     * @param array $message
     * @return bool
     */
    public function validate(Account $creator, array $message): bool
    {
        if (! isset($message['signature'])) {
            return false;
        }

        /** @var array<string> $signature */
        $signature = $message['signature'];
        $signatureValue = $signature['signatureValue'];
        unset($message['signature']);

        if ($signature['type'] != 'RsaSignature2017') {
            return false;
        }

        // Unset the things that are not signed
        unset($signature['type']);
        unset($signature['id']);
        unset($signature['signatureValue']);
        $signature['@context'] = 'https://w3id.org/security/v1';

        // Create the hash of both the message and the signature
        $messageHash = $this->hash($this->canonicalize($signature)) . $this->hash($this->canonicalize($message));

        $ret = openssl_verify($messageHash, base64_decode($signatureValue), $creator->getPublicKeyPem() ?? '', OPENSSL_ALGO_SHA256);
        dd($ret);
        return $ret == 1;
    }

    public function createHashDigest(string $message): string
    {
        // Hashing the message does not require canonicalization. We just need to make sure we send the message as-is within the HTTP POST body
        return "SHA-256=" . base64_encode(hash('sha256', $message, true));
    }
}
