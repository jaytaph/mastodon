<?php

namespace App\Command;

use GuzzleHttp\Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $msg = $input->getArgument('msg');

        $dt = new \DateTime();
        $date = $dt->format('D, d M Y H:i:s T');

        $senderKey = "https://dhpt.nl/users/jaytaph#main-key";
        $senderUrl = "https://dhpt.nl/users/jaytaph";

//        $receiverUrl = "https://toet.dnzm.nl/users/max";
//        $receiverPath = "/users/max/inbox";
//        $receiverHost = "toet.dnzm.nl";
        $receiverUrl = "https://mastodon.nl/users/jaytest/inbox";
        $receiverPath = "/users/jaytest/inbox";
        $receiverHost = "mastodon.nl";


        $data = [
            "@context" => "https://www.w3.org/ns/activitystreams",
            "id" => "https://dhpt.nl/users/jaytaph/statuses/2",
            "type" => "Follow",
            "actor" => $senderUrl,
            "object" => $receiverUrl,
        ];

        $msgDigest = base64_encode(hash("sha256", json_encode($data)));

        $sigText = "(request-target): post {$receiverPath}\ndigest: SHA-256={$msgDigest}\nhost: {$receiverHost}\ndate: {$date}";
        openssl_sign($sigText, $signature, file_get_contents("private.pem"), OPENSSL_ALGO_SHA256);
        $signature = base64_encode($signature);
        $sigHeader = "keyId=\"{$senderKey}\",algorithm=\"rsa-sha256\",headers=\"(request-target) digest host date\",signature=\"{$signature}\"";

        $headers = [
            'Date' => $date,
            'Content-Type' => 'application/activity+json',
            'Host' => $receiverHost,
            'Digest' => "SHA-256={$msgDigest}",
            'Signature' => $sigHeader,
        ];

        try {
            $client = new Client();
            $result = $client->post($receiverUrl, [
                'debug' => true,
                'headers' => $headers,
                'json' => $data,
            ]);
        } catch (\Throwable $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        $io->writeln($result->getBody());

        $io->success('all done??');



//        $data = [
//            '@context' => 'https://www.w3.org/ns/activitystreams',
//            "id" => "https://dhpt.nl/users/jaytaph/statuses/1",
//            "type" => "Create",
//            "actor" => "https://dhpt.nl/users/jaytaph",
//            "object" => [
//                "id" => "https://dhpt.nl/users/jaytaph/statuses/1",
//                "type" => "Note",
//                "published" => (new \DateTimeImmutable())->format(\DateTime::ATOM),
//                "attributedTo" => "https://dhpt.nl/users/jaytaph",
//                "content" => $arg1,
//                "to" => "https://www.w3.org/ns/activitystreams#Public",
//            ]
//        ];


        $io->success('');

        return Command::SUCCESS;
    }
}
