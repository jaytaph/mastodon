<?php

namespace App\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

class AuthClientService
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client();
//        $this->client = $client;
    }

    public function fetch(string $senderUrl, string $href): ?Response
    {
        $dt = new \DateTime("now", new \DateTimeZone('GMT'));
        $date = $dt->format('D, d M Y H:i:s T');

        $senderKey = $senderUrl . "#main-key";

        $receiverUrl = $href;
        $receiverPath = parse_url($receiverUrl, PHP_URL_PATH);
        $receiverHost = parse_url($receiverUrl, PHP_URL_HOST);

        // Sign the headers with the users private key for authenticity
        $sigText = "(request-target): get {$receiverPath}\nhost: {$receiverHost}\ndate: {$date}";
        openssl_sign($sigText, $signature, file_get_contents("private.pem"), OPENSSL_ALGO_SHA256);
        $signature = base64_encode($signature);

        // Create signature HTTP header which defines the signature, the key used and the algorithm used and which headers it contains
        $sigHeader = "keyId=\"{$senderKey}\",algorithm=\"rsa-sha256\",headers=\"(request-target) host date\",signature=\"{$signature}\"";

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
}
