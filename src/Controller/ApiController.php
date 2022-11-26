<?php

declare(strict_types=1);

namespace App\Controller;

use App\Config;
use App\Service\AccountService;
use Doctrine\ORM\EntityNotFoundException;
use League\Bundle\OAuth2ServerBundle\Manager\ClientManagerInterface;
use League\Bundle\OAuth2ServerBundle\Model\Client;
use League\Bundle\OAuth2ServerBundle\ValueObject\Grant;
use League\Bundle\OAuth2ServerBundle\ValueObject\RedirectUri;
use League\Bundle\OAuth2ServerBundle\ValueObject\Scope;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

class ApiController extends AbstractController
{
    use AccountTrait;

    protected AccountService $accountService;
    protected LoggerInterface $logger;

    public function __construct(AccountService $accountService, LoggerInterface $logger)
    {
        $this->accountService = $accountService;
        $this->logger = $logger;
    }

    #[Route('/api/v1/accounts/verify_credentials', name: 'api_verify_credentials')]
    #[IsGranted('ROLE_OAUTH2_READ')]
    public function verifyCredentials(): Response
    {
        $user = $this->getUser();
        if (is_null($user)) {
            throw $this->createNotFoundException();
        }

        $account = $this->accountService->findAccount($user->getUserIdentifier());
        if (!$account) {
            throw $this->createNotFoundException();
        }

        return new JsonResponse($this->accountService->toJson($account));
    }

    #[Route('/api/v1/lists', name: 'api_lists')]
    #[IsGranted('ROLE_OAUTH2_READ')]
    public function lists(): Response
    {
        $data = [
            [
                'id' => '1',
                'title' => 'Test List',
                'replies_policy' => 'public',
            ],
            [
                'id' => '2',
                'title' => 'Test List 2',
                'replies_policy' => 'public',
            ]
        ];

        return new JsonResponse($data);
    }

    #[Route('/api/v1/follow_requests', name: 'api_follow_requests')]
    #[IsGranted('ROLE_OAUTH2_READ')]
    #[IsGranted('ROLE_OAUTH2_FOLLOW')]
    public function followRequests(): Response
    {
        $data = [
        ];

        return new JsonResponse($data);
    }

    #[Route('/api/v1/filters', name: 'api_filters')]
    #[IsGranted('ROLE_OAUTH2_READ')]
    public function filters(): Response
    {
        $data = [
        ];

        return new JsonResponse($data);
    }

    #[Route('/api/v1/custom_emojis', name: 'api_emojis')]
    #[IsGranted('PUBLIC_ACCESS')]
    public function customEmojis(): Response
    {
        $data = [
              [
                'shortcode' => 'aaaa',
                'url' => 'https://files.mastodon.social/custom_emojis/images/000/007/118/original/aaaa.png',
                'static_url' => 'https://files.mastodon.social/custom_emojis/images/000/007/118/static/aaaa.png',
                'visible_in_picker' => true
              ],
              [
                'shortcode' => 'AAAAAA',
                'url' => 'https://files.mastodon.social/custom_emojis/images/000/071/387/original/AAAAAA.png',
                'static_url' => 'https://files.mastodon.social/custom_emojis/images/000/071/387/static/AAAAAA.png',
                'visible_in_picker' => true
              ],
              [
                'shortcode' => 'blobaww',
                'url' => 'https://files.mastodon.social/custom_emojis/images/000/011/739/original/blobaww.png',
                'static_url' => 'https://files.mastodon.social/custom_emojis/images/000/011/739/static/blobaww.png',
                'visible_in_picker' => true,
                'category' => 'Blobs'
              ],
              [
                'shortcode' => 'yikes',
                'url' => 'https://files.mastodon.social/custom_emojis/images/000/031/275/original/yikes.png',
                'static_url' => 'https://files.mastodon.social/custom_emojis/images/000/031/275/static/yikes.png',
                'visible_in_picker' => true
              ],
              [
                'shortcode' => 'ziltoid',
                'url' => 'https://files.mastodon.social/custom_emojis/images/000/017/094/original/05252745eb087806.png',
                'static_url' => 'https://files.mastodon.social/custom_emojis/images/000/017/094/static/05252745eb087806.png',
                'visible_in_picker' => true
              ]
        ];

        return new JsonResponse($data);
    }

    #[Route('/api/v1/timelines/home', name: 'api_timeline_home')]
    #[IsGranted('ROLE_OAUTH2_READ')]
    public function timelineHome(): Response
    {
        $data = [
        ];

        return new JsonResponse($data);
    }

    #[Route('/api/v1/timelines/public', name: 'api_timeline_public')]
    #[IsGranted('ROLE_USER')]
    public function timelinePublic(): Response
    {
        $data = [
        ];

        return new JsonResponse($data);
    }

    #[Route('/api/v1/timelines/tag/{tag}', name: 'api_timeline_tag')]
    #[IsGranted('ROLE_OAUTH2_READ')]
    public function timelineTag(): Response
    {
        $data = [
        ];

        return new JsonResponse($data);
    }

    #[Route('/api/v1/markers', name: 'api_markers')]
    #[IsGranted('ROLE_OAUTH2_READ')]
    public function markers(): Response
    {
        $data = [
        ];

        return new JsonResponse($data);
    }

    #[Route('/api/v1/notifications', name: 'api_notifications')]
    #[IsGranted('ROLE_OAUTH2_READ')]
    public function notifications(): Response
    {
        $data = [
        ];

        return new JsonResponse($data);
    }

    #[Route('/api/v1/accounts/{id}', name: 'api_account')]
    #[IsGranted('PUBLIC_ACCESS')]
    public function account(string $id): Response
    {
        $uuid = Uuid::fromString($id);
        $account = $this->accountService->findAccountById($uuid);
        if (!$account) {
            throw $this->createNotFoundException();
        }

        return new JsonResponse($this->accountService->toJson($account));
    }

    #[Route('/api/v1/accounts/{id}/statuses', name: 'api_account_statuses')]
    #[IsGranted('PUBLIC_ACCESS')]
    #[IsGranted('ROLE_OAUTH2_READ')]
    public function statuses(string $id): Response
    {
        $uuid = Uuid::fromString($id);
        $account = $this->accountService->findAccountById($uuid);
        if (!$account) {
            throw $this->createNotFoundException();
        }

        // Only return public statuses when we are not logged in
//        $publicOnly = !$this->isGranted('ROLE_OAUTH2_READ');

        $data = [];

        return new JsonResponse($data);
    }

    #[Route('/api/v1/accounts/{acct}/following', name: 'api_account_following')]
    #[IsGranted('ROLE_OAUTH2_READ')]
    public function following(string $acct): Response
    {
        $account = $this->findAccount($acct, localOnly: true);

        $ret = [];
        foreach ($this->accountService->getFollowing($account) as $follower) {
            $ret[] = $this->accountService->toJson($follower);
        }

        return new JsonResponse($ret);
    }

    #[Route('/api/v1/accounts/{acct}/followers', name: 'api_account_followers')]
    #[IsGranted('ROLE_OAUTH2_READ')]
    public function followers(string $acct): Response
    {
        $account = $this->findAccount($acct, localOnly: true);

        $ret = [];
        foreach ($this->accountService->getFollowers($account) as $follower) {
            $ret[] = $this->accountService->toJson($follower);
        }

        return new JsonResponse($ret);
    }


    /**
     * @throws \Exception
     */
    #[Route('/api/v1/apps', name: 'api_apps', methods: ['POST'])]
    #[IsGranted('PUBLIC_ACCESS')]
    public function apps(ClientManagerInterface $clientManager, Request $request): Response
    {
        $id = bin2hex(random_bytes(16));
        $secret = 'dhpt_secret_' . bin2hex(random_bytes(32));

        $client = new Client(strval($request->get('client_name')), $id, $secret);
        $client->setActive(true);

        $scopes = explode(' ', strval($request->get('scopes')));
        $grants = ['authorization_code', 'refresh_token'];

        $client
            ->setRedirectUris(...array_map(static function (string $redirectUri): RedirectUri {
                try {
                    return new RedirectUri($redirectUri);
                } catch (\Throwable) {
                    // @TODO: Handle invalid redirect URI
                    return new RedirectUri('https://localhost');
                }
            }, explode(' ', strval($request->get('redirect_uris')))));
        $client
            ->setGrants(...array_map(static function (string $grant): Grant {
                return new Grant($grant);
            }, $grants));
        $client
            ->setScopes(...array_map(static function (string $scope): Scope {
                return new Scope($scope);
            }, $scopes))
        ;

        $clientManager->save($client);

        $data = [
            'name' => $client->getName(),
            'client_id' => $client->getIdentifier(),
            'client_secret' => $client->getSecret(),
        ];

        return new JsonResponse($data);
    }

    /**
     * @throws EntityNotFoundException
     */
    #[Route('/api/v1/instance', name: 'api_instance')]
    #[IsGranted('PUBLIC_ACCESS')]
    public function instance(): Response
    {
        $adminAccount = $this->accountService->getAccount(Config::ADMIN_USER);

        $data = [
            'uri' => Config::SITE_DOMAIN,
            'title' => 'DonkeyHeads Mastodon Instance',
            'description' => 'Server written in PHP, so what could possibly go wrong!??',
            'short_description' => 'Run by DonkeyHeads',
            'email' => 'jthijssen@blafhoest.nl',
            'version' => '4.0.1',
            'languages' => [
                'en',
                'nl',
            ],
            'registrations' => false,
            'approval_required' => true,
            'invites_enabled' => true,
            'urls' => [
                'streaming_api' => 'wss://' . Config::SITE_DOMAIN . '/api/v1/streaming',
            ],
            'stats' => [
                'user_count' => 2,
                'status_count' => 1234,
                'domain_count' => 1,
            ],
            'thumbnail' => 'https://dhpt.nl/dh.jpg',
            'contact_account' => $this->accountService->toJson($adminAccount),
        ];

        return new JsonResponse($data);
    }
}
