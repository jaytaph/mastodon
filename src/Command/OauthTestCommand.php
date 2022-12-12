<?php

declare(strict_types=1);

namespace App\Command;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Uid\Uuid;
use Jaytaph\TypeArray\TypeArray;

#[AsCommand(
    name: 'app:test:oauth',
    description: 'Add a short description for your command',
)]
class OauthTestCommand extends Command
{
    protected function configure(): void
    {
    }

    /**
     * @throws GuzzleException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $client = new Client();

        $url = "localhost:8000";

        $response = $client->post("https://$url/api/v1/apps", [
            "headers" => [
                "Accept" => "application/json",
                "Content-Type" => "application/json",
            ],
            "body" => json_encode([
                "client_name" => "Simple Test App",
                "redirect_uris" => "https://dhpt.nl",
                "scopes" => "read write follow",
                "website" => "https://dhpt.nl",
            ]),
        ]);
        $response = TypeArray::fromJson($response->getBody()->getContents());

        $clientId = $response->getStringOrNull("[client_id]");
        $clientSecret = $response->getStringOrNull("[client_secret]");

        $params = [
            "client_id" => $clientId,
            "redirect_uri" => "https://dhpt.nl",
            "response_type" => "code",
            "scope" => "read write follow",
            "state" => Uuid::v4(),
        ];

        $authUrl = 'https://' . $url . '/oauth/authorize?' . http_build_query($params);

        $response = $client->get($authUrl, [
            'allow_redirects' => false,
            'debug' => true,
            "headers" => [
                "Accept" => "application/json",
                "Content-Type" => "application/json",
            ],
            "cookies" => CookieJar::fromArray([
                'PHPSESSID' => 'o7u7h0q5jf3aopafp232njb74t',
            ], 'localhost'),
        ]);

        $code = substr($response->getHeader("Location")[0], strpos($response->getHeader("Location")[0], "code=") + 5);

        $response = $client->post('https://' . $url . '/oauth/token', [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode([
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'grant_type' => 'authorization_code',
                'redirect_uri' => 'https://dhpt.nl',
                'code' => $code,
            ]),
        ]);

        $response = TypeArray::fromJson($response->getBody()->getContents());

        $token = $response->getStringOrNull('[access_token]');
        $response = $client->get('https://' . $url . '/api/v1/accounts/verify_credentials', [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);
        $response = json_decode($response->getBody()->getContents(), true);
        $io->success(strval($response));

        return Command::SUCCESS;
    }
}
