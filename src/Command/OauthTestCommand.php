<?php

declare(strict_types=1);

namespace App\Command;

use App\ActivityPub;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Uid\Uuid;

#[AsCommand(
    name: 'app:test:oauth',
    description: 'Add a short description for your command',
)]
class OauthTestCommand extends Command
{
    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $client = new Client();

        $response = $client->post("https://localhost:8000/api/v1/apps", [
            "headers" => [
                "Accept" => "application/json",
                "Content-Type" => "application/json",
            ],
            "body" => json_encode([
                "client_name" => "Simple Test App",
                "redirect_uris" => "https://localhost:8000",
                "scopes" => "read write follow",
                "website" => "https://localhost:8000",
            ]),
        ]);

        $response = json_decode($response->getBody()->getContents(), true);

        $clientId = $response["client_id"];
        $clientSecret = $response["client_secret"];

        $params = [
            "client_id" => $clientId,
            "redirect_uri" => "https://localhost:8000",
            "response_type" => "code",
            "scope" => "read write follow",
            "state" => Uuid::v4(),
        ];

        $url = 'https://localhost:8000/oauth/authorize?' . http_build_query($params);

        $response = $client->get($url, [
            'allow_redirects' => false,
            'debug' => true,
            "headers" => [
                "Accept" => "application/json",
                "Content-Type" => "application/json",
            ],
            "cookies" => CookieJar::fromArray([
                'PHPSESSID' => 'ormn5gt30s118cae7skn054263',
            ], "localhost"),
        ]);

        $code = substr($response->getHeader("Location")[0], strpos($response->getHeader("Location")[0], "code=") + 5);

        $body = json_encode([
                        'client_id' => $clientId,
                        'client_secret' => $clientSecret,
                        'grant_type' => 'authorization_code',
                        'redirect_uri' => 'https://localhost:8000',
                        'code' => $code,
                    ]);

        $response = $client->post('https://localhost:8000/oauth/token', [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode([
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'grant_type' => 'authorization_code',
                'redirect_uri' => 'https://localhost:8000',
                'code' => $code,
            ]),
        ]);
        $response = json_decode($response->getBody()->getContents(), true);
        dump($response);

        $token = $response['access_token'];
        $response = $client->get('https://localhost:8000/api/v1/accounts/verify_credentials', [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);
        dump($response->getStatusCode());
        $response = json_decode($response->getBody()->getContents(), true);
        dump($response);

        return Command::SUCCESS;
    }
}
