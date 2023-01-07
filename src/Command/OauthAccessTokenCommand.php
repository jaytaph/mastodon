<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\ConfigService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Uid\Uuid;
use Jaytaph\TypeArray\TypeArray;

#[AsCommand(
    name: 'app:test:oauth',
    description: 'Fetches an access token from given mastodon server',
)]
class OauthAccessTokenCommand extends Command
{
    protected ConfigService $configService;

    public function __construct(ConfigService $configService)
    {
        parent::__construct();
        $this->configService = $configService;
    }


    protected function configure(): void
    {
        $this
            ->addArgument('url', InputArgument::REQUIRED, 'The URL of the Mastodon instance')
            ->addOption('verify', null, InputOption::VALUE_NONE, 'verify the account by making an authenticated API call')
        ;
    }

    /**
     * @throws GuzzleException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $client = new Client();

        $redirUris = [
            'mastodon-android-auth://callback',
        ];

        $response = $this->createApp($client, strval($input->getArgument('url')), $redirUris);

        $clientId = $response->getStringOrNull("[client_id]");
        $clientSecret = $response->getStringOrNull("[client_secret]");

        $params = [
            "client_id" => $clientId,
            "redirect_uri" => $redirUris[0], //$this->configService->getConfig()->getSiteUrl(),
            "response_type" => "code",
            "scope" => "read write follow push",
            "state" => Uuid::v4(),
        ];
        $authUrl = 'https://' . $input->getArgument('url') . '/oauth/authorize?' . http_build_query($params);

        $io->writeln("Please visit the following URL and authorize the app:");
        $io->writeln($authUrl);

        $code = $io->ask("Please enter the code you received from the authorization page");

        $response = $this->getAccessToken($client, strval($input->getArgument('url')), $clientId, $clientSecret, strval($code));
        dump([
            "access_token" => $response->getStringOrNull("[access_token]"),
            "token_type" => $response->getStringOrNull("[token_type]"),
            "scope" => $response->getStringOrNull("[scope]"),
            "created_at" => $response->getIntOrNull("[created_at]"),
        ]);

        if ($input->getOption('verify')) {
            $response = $this->verifyAccessToken($client, strval($input->getArgument('url')), $response->getStringOrNull("[access_token]"));
            dump($response->toArray());
        }

        return Command::SUCCESS;
    }

    /**
     * @param string[] $redirectUris
     */
    protected function createApp(Client $client, string $url, array $redirectUris): TypeArray
    {
        $response = $client->post("https://$url/api/v1/apps", [
            "headers" => [
                "Accept" => "application/json",
                "Content-Type" => "application/json",
            ],
            "body" => json_encode([
                "client_name" => "Simple Test App",
                "redirect_uris" => join(" ", $redirectUris),
                "scopes" => "read write follow",
                "website" => "https://dhpt.nl",
            ]),
        ]);

        return TypeArray::fromJson($response->getBody()->getContents());
    }

    protected function getAccessToken(Client $client, string $url, ?string $clientId, ?string $clientSecret, string $code): TypeArray
    {
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

        return TypeArray::fromJson($response->getBody()->getContents());
    }

    protected function verifyAccessToken(Client $client, string $url, ?string $token): TypeArray
    {
        $response = $client->get('https://' . $url . '/api/v1/accounts/verify_credentials', [
                'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
                ],
        ]);

        return TypeArray::fromJson($response->getBody()->getContents());
    }
}
