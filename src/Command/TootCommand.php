<?php

declare(strict_types=1);

namespace App\Command;

use App\ActivityStream;
use GuzzleHttp\Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Uid\Uuid;

#[AsCommand(
    name: 'app:toot',
    description: 'Add a short description for your command',
)]
class TootCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument('msg', InputArgument::OPTIONAL, 'Argument description')
        ;
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $msg = $input->getArgument('msg');

        $dt = new \DateTime("now", new \DateTimeZone('GMT'));
        $date = $dt->format(ActivityStream::DATETIME_FORMAT);

        $senderKey = "https://dhpt.nl/users/jaytaph#main-key";
        $senderUrl = "https://dhpt.nl/users/jaytaph";

        $receiverUrl = "https://toet.dnzm.nl/users/max";
//        $receiverUrl = "https://phpc.social/users/Skoop";
//        $receiverUrl = "https://mastodon.nl/users/jaytest";
        $receiverPath = parse_url($receiverUrl, PHP_URL_PATH);
        $receiverHost = parse_url($receiverUrl, PHP_URL_HOST);

        $obj = [
            'attachment' => [],
            'type' => 'Note',
            'summary' => null,
            'inReplyTo' => null,
            'published' => $dt->format(ActivityStream::DATETIME_FORMAT),
            'attributedTo' => $senderUrl,
            'to' => [
                $senderUrl . '/followers',
                $receiverUrl,
            ],
            'cc' => [],
            'sensitive' => false,
            'content' => '<p>' . $msg . '</p>',
            'tag' => [],
            'id' => "https://dhpt.nl/users/jaytaph/posts/" . Uuid::v4(),
        ];

        // Set data and create signature for the message body
        $data = [
            '@context' => "https://www.w3.org/ns/activitystreams",
            'actor' => $senderUrl,
            'cc' => [
                $receiverUrl,
            ],
            'id' => "https://dhpt.nl/users/jaytaph/posts/" . Uuid::v4(),
            'object' => $obj,
            'published' => $dt->format(ActivityStream::DATETIME_FORMAT),
            'to' => 'https://www.w3.org/ns/activitystreams#Public',
            'type' => 'Create',
        ];
        $data = (string)json_encode($data);
        $msgDigest = "SHA-256=" . base64_encode(hash("sha256", $data, true));

        // Sign the headers with the users private key for authenticity
        $sigText = "(request-target): post $receiverPath/inbox\nhost: $receiverHost\ndate: $date\ndigest: $msgDigest";
        openssl_sign($sigText, $signature, (string)file_get_contents("private.pem"), OPENSSL_ALGO_SHA256);
        $signature = base64_encode($signature);

        // Create signature HTTP header which defines the signature, the key used and the algorithm used and which headers it contains
        $sigHeader = "keyId=\"$senderKey\",algorithm=\"rsa-sha256\",headers=\"(request-target) host date digest\",signature=\"$signature\"";

        // Set HTTP headers to send
        $headers = [
            'Date' => $date,
            'Digest' => $msgDigest,
            'Host' => $receiverHost,
            'Signature' => $sigHeader,
            'Content-Type' => 'application/activity+json',
            'Accept' => 'application/activity+json',
        ];

        // Send data to the receiver
        try {
            $client = new Client();
            $result = $client->post($receiverUrl . "/inbox", [
                'debug' => true,
                'headers' => $headers,
                'json' => $data,
                'http_errors' => false,
            ]);
        } catch (\Throwable $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        $io->note((string)$result->getStatusCode());
        $io->note((string)$result->getBody());

        $io->success('All done');
        return Command::SUCCESS;
    }
}
