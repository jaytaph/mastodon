<?php

declare(strict_types=1);

namespace App\Command;

use App\ActivityPub;
use GuzzleHttp\Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:account:fetch',
    description: 'Retrieve an account from a remote server',
)]
class AccountCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument('url', InputArgument::REQUIRED, 'User url to accept the follow')
        ;
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $url = strval($input->getArgument('url'));

        $dt = new \DateTime("now", new \DateTimeZone('GMT'));
        $date = $dt->format(ActivityPub::DATETIME_FORMAT);

        $senderKey = "https://dhpt.nl/users/jaytaph#main-key";
//        $senderUrl = "https://dhpt.nl/users/jaytaph";
//        $senderPath = parse_url($senderUrl, PHP_URL_PATH);
//        $senderHost = parse_url($senderUrl, PHP_URL_HOST);

        $receiverUrl = $url;
        $receiverPath = parse_url($receiverUrl, PHP_URL_PATH);
        $receiverHost = parse_url($receiverUrl, PHP_URL_HOST);

        // Sign the headers with the users private key for authenticity
        $sigText = "(request-target): get $receiverPath\nhost: $receiverHost\ndate: $date";
        $key = strval(file_get_contents("private.pem"));
        openssl_sign($sigText, $signature, $key, OPENSSL_ALGO_SHA256);
        $signature = base64_encode($signature);
        print_r($signature);

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
                'debug' => true,
                'headers' => $headers,
                'http_errors' => false,
                'verify' => false,
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
