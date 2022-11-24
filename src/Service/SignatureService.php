<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\SignatureValidationException;
use ML\JsonLD\JsonLD;
use ML\JsonLD\NQuads;

class SignatureService
{
    protected AccountService $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
    }

    /**
     * @param array<string> $message
     */
    public function hasSignature(array $message): bool
    {
        return isset($message['signature']);
    }

    /**
     * @param array<string> $message
     */
    public function validateMessage(array $message): bool
    {
        if (! isset($message['signature'])) {
            throw SignatureValidationException::noSignature();
        }

        /** @var array<string> $signature */
        $signature = $message['signature'];
        $signatureValue = $signature['signatureValue'];
        unset($message['signature']);

        if ($signature['type'] != 'RsaSignature2017') {
            throw SignatureValidationException::incorrectType();
        }

        // Fetch the creator of the message/signature so we have the key
        $pos = strpos($signature['creator'], '#');
        $creator = $pos ? substr($signature['creator'], 0, $pos) : $signature['creator'];
        $account = $this->accountService->findAccount($creator);
        if (!$account) {
            throw SignatureValidationException::accountNotFound($creator);
        }

        // Unset the things that are not signed
        unset($signature['type']);
        unset($signature['id']);
        unset($signature['signatureValue']);
        $signature['@context'] = 'https://w3id.org/security/v1';

        // Create the hash of both the message and the signature
        $cS = $this->canonicalize($signature);
        $cM = $this->canonicalize($message);
        $hash = $this->hash($cS . $cM);

        $ret = openssl_verify($hash, base64_decode($signatureValue), $account->getPublicKeyPem() ?? '', OPENSSL_ALGO_SHA256);

        return $ret == 1;
    }

    protected function hash(string $data): string
    {
        return bin2hex(hash('sha256', $data, true));
    }

    /**
     * Converts JSON-LD to a canonical form
     *
     * @param array<string> $data
     */
    protected function canonicalize(array $data): string
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
}
