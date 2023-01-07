<?php

declare(strict_types=1);

namespace App\Service;

use App\ActivityStream;
use App\Entity\Account;
use GuzzleHttp\Client;
use Jaytaph\TypeArray\TypeArray;
use Psr\Http\Message\ResponseInterface;

class AuthClientService
{
    protected Client $client;
    protected SignatureService $signatureService;

    public function __construct(Client $client = null, SignatureService $signatureService)
    {
        $this->client = $client ?? new Client([]);
        $this->signatureService = $signatureService;
    }

    /**
     * @throws \Exception
     */
    public function fetch(Account $account, string $href): ?ResponseInterface
    {
        $dt = new \DateTime("now", new \DateTimeZone('GMT'));
        $date = $dt->format(ActivityStream::DATETIME_FORMAT_GMT);

        $senderKey = $account->getUri() . "#main-key";

        $receiverUrl = $href;
        $receiverPath = parse_url($receiverUrl, PHP_URL_PATH);
        $receiverHost = parse_url($receiverUrl, PHP_URL_HOST);

        // Sign the headers with the users private key for authenticity
        $sigText = "(request-target): get $receiverPath\nhost: $receiverHost\ndate: $date";
        openssl_sign($sigText, $signature, $account->getPrivateKeyPem() ?? '', OPENSSL_ALGO_SHA256);
        $signature = base64_encode($signature);

        // Create signature HTTP header which defines the signature, the key used and the algorithm used and which headers it contains
        $sigHeader = "keyId=\"$senderKey\",algorithm=\"rsa-sha256\",headers=\"(request-target) host date\",signature=\"$signature\"";

        // Set HTTP headers to send
        $headers = [
            'Date' => $date,
            'Host' => $receiverHost,
            'Signature' => $sigHeader,
            'Accept' => 'application/activity+json',
        ];

        // Send data to the receiver
        try {
            $client = new Client();
            $result = $client->get($receiverUrl, [
                'headers' => $headers,
                'http_errors' => false,
            ]);
        } catch (\Throwable) {
            return null;
        }

        return $result;
    }

    /**
     * @throws \Exception
     */
    public function send(Account $source, Account $recipient, TypeArray $message): ?ResponseInterface
    {
        $recipientUri = $recipient->getUri() . '/inbox';
        return $this->sendToUri($source, $recipientUri, $message);
    }

    /**
     * @throws \Exception
     */
    public function sendToUri(Account $source, string $recipientUri, TypeArray $message): ?ResponseInterface
    {
        $recipientPath = parse_url($recipientUri, PHP_URL_PATH);
        $recipientHost = parse_url($recipientUri, PHP_URL_HOST);

        $dt = new \DateTime("now", new \DateTimeZone('GMT'));
        $date = $dt->format(ActivityStream::DATETIME_FORMAT_GMT);

        $senderKey = $source->getUri() . "#main-key";

        $message = json_encode($message);
        if (!$message) {
            $message = '';
        }
        $msgDigest = $this->signatureService->createHashDigest($message);

        // Sign the headers with the users private key for authenticity
        $sigText = "(request-target): post $recipientPath\nhost: $recipientHost\ndate: $date\ndigest: $msgDigest";
        openssl_sign($sigText, $signature, $source->getPrivateKeyPem() ?? '', OPENSSL_ALGO_SHA256);
        $signature = base64_encode($signature);

        // Create signature HTTP header which defines the signature, the key used and the algorithm used and which headers it contains
        $sigHeader = "keyId=\"$senderKey\",algorithm=\"rsa-sha256\",headers=\"(request-target) host date digest\",signature=\"$signature\"";

        // Set HTTP headers to send
        $headers = [
            'Date' => $date,
            'Digest' => $msgDigest,
            'Host' => $recipientHost,
            'Signature' => $sigHeader,
            'Accept' => 'application/activity+json',
            'Content-Type' => 'application/activity+json',
        ];

        // Send data to the receiver
        try {
            $client = new Client();
            $result = $client->post($recipientUri, [
                'headers' => $headers,
                'http_errors' => false,
                'body' => $message,
            ]);
        } catch (\Throwable) {
            return null;
        }

        return $result;
    }
}
