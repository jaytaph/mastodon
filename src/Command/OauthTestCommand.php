<?php

declare(strict_types=1);

namespace App\Command;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
        $client = new Client();

//        $response = $client->post("https://mastodon.nl/api/v1/apps", [
//            "headers" => [
//                "Accept" => "application/json",
//                "Content-Type" => "application/json",
//            ],
//            "body" => json_encode([
//                "client_name" => "Simple Test App",
//                "redirect_uris" => "https://dhpt.nl",
//                "scopes" => "read write follow",
//                "website" => "https://dhpt.nl",
//            ]),
//        ]);
//        $response = json_decode($response->getBody()->getContents(), true);
//
//        /** @var array<string> $response */
//        $clientId = $response["client_id"];
//        $clientSecret = $response["client_secret"];

        $clientId = 'Fyq_ldNW3FCaR9UigGvo1vZdj3AU9B13aLrq19j8wO8';
        $clientSecret = 'Q0mXK5M_aZhBf8MA4HiXMrZzL4-wCGtDAE8BBEhE5Es';

//        $params = [
//            "client_id" => $clientId,
//            "redirect_uri" => "https://dhpt.nl",
//            "response_type" => "code",
//            "scope" => "read write follow",
//            "state" => Uuid::v4(),
//        ];
//
//        $url = 'https://mastodon.nl/oauth/authorize?' . http_build_query($params);
//
//        $response = $client->get($url, [
//            'allow_redirects' => false,
//            'debug' => true,
//            "headers" => [
//                "Accept" => "application/json",
//                "Content-Type" => "application/json",
//            ],
//            "cookies" => CookieJar::fromArray([
////                '_session_id' => 'eyJfcmFpbHMiOnsibWVzc2FnZSI6IklqZGtOamd5TkRNMU5qazFORGc1WlRZMU1tUTJNalJpWXpRNVpXSXpNbUkwSWc9PSIsImV4cCI6IjIwMjMtMTEtMjdUMDg6MzU6MjguMzM2WiIsInB1ciI6ImNvb2tpZS5fc2Vzc2lvbl9pZCJ9fQ%3D%3D--b803ee91e136e1d0eff0e8a4784bfdb8fc04196c',
////                '_mastondon_session' => 'qqyrPrExmfYSUikyI%2FNh83NyVnSXseKhc4BhbWFc%2FHfsDQGdRCo22ds9eljkLqU2aG3D7X6FmKqnfKi%2BEp%2BWKjS9rahLo22BJ0Xny4fyfuFnYjJSyMW0oBqGOkAWxVICwfRBgXy1BveYTHJMicgB75wwbZZh2urQlobQpX2mavkNzQ4t65cVDzBz9N7nT3VPS1V773FGIFEezo3NyyWEt%2Fe%2Fj1cvtcAUuT6kLUaSqmPA8hlLAmEiEB1ZaRlNGi8HpCINUmJvKHNB78fCV67gU%2BMNL4ghxObNgfggQSp%2FcnJeOJcktDued91QB9zWJ%2FFaT9YbQLySawSqFV%2BOmtiaejubFbTJx64r56KYvoW0qGUOKoVAZDdVGJdlrQ%2BKYnqQIWezwVpuXHQOP%2BhVPSxFEFViq8rkik86OJK7FUwfCt%2FwoWpAk6psYsVctk%2B7--PDURI2dpp%2BWzaQA%2F--ER3s3GiG5Ah9MmSENalJHQ%3D%3D',
//            ], 'mastodon.nl'),
//        ]);

//        $code = substr($response->getHeader("Location")[0], strpos($response->getHeader("Location")[0], "code=") + 5);
//        dump($code);

        $code = 'nv_noKKrli9XN5rRjJiGaTyR43lKZ6x6NcOGF9UgtKQ';

        $response = $client->post('https://mastodon.nl/oauth/token', [
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

        $response = json_decode($response->getBody()->getContents(), true);
        dump($response);

        /** @var array<string> $response */
        $token = $response['access_token'];
        $response = $client->get('https://mastodon.nl/api/v1/accounts/verify_credentials', [
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
