<?php

namespace App\Controller;

use App\Config;
use App\Service\AccountService;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

class ApiController extends AbstractController
{
    protected AccountService $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
    }

    // @TODO: THis does not have any security checks yet
    #[Route('/api/v1/accounts/verify_credentials', name: 'api_verify_credentials')]
    public function verifyCredentials(Request $request)
    {
        $account = $this->accountService->getAccount(Config::ADMIN_USER);
        if (!$account) {
            throw new NotFoundHttpException();
        }

        return new JsonResponse($this->accountService->toJson($account));
    }

    // @TODO: THis does not have any security checks yet
    #[Route('/api/v1/lists', name: 'api_lists')]
    public function lists(Request $request)
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
    public function followRequests(Request $request)
    {
        $data = [
        ];

        return new JsonResponse($data);
    }

    #[Route('/api/v1/filters', name: 'api_filters')]
    public function filters(Request $request)
    {
        $data = [
        ];

        return new JsonResponse($data);
    }

    #[Route('/api/v1/custom_emojis', name: 'api_emojis')]
    public function customEmojis(Request $request)
    {
        $data = [
              [
                "shortcode" => "aaaa",
                "url" => "https://files.mastodon.social/custom_emojis/images/000/007/118/original/aaaa.png",
                "static_url" => "https://files.mastodon.social/custom_emojis/images/000/007/118/static/aaaa.png",
                "visible_in_picker" => true
              ],
              [
                "shortcode" => "AAAAAA",
                "url" => "https://files.mastodon.social/custom_emojis/images/000/071/387/original/AAAAAA.png",
                "static_url" => "https://files.mastodon.social/custom_emojis/images/000/071/387/static/AAAAAA.png",
                "visible_in_picker" => true
              ],
              [
                "shortcode" => "blobaww",
                "url" => "https://files.mastodon.social/custom_emojis/images/000/011/739/original/blobaww.png",
                "static_url" => "https://files.mastodon.social/custom_emojis/images/000/011/739/static/blobaww.png",
                "visible_in_picker" => true,
                "category" => "Blobs"
              ],
              [
                "shortcode" => "yikes",
                "url" => "https://files.mastodon.social/custom_emojis/images/000/031/275/original/yikes.png",
                "static_url" => "https://files.mastodon.social/custom_emojis/images/000/031/275/static/yikes.png",
                "visible_in_picker" => true
              ],
              [
                "shortcode" => "ziltoid",
                "url" => "https://files.mastodon.social/custom_emojis/images/000/017/094/original/05252745eb087806.png",
                "static_url" => "https://files.mastodon.social/custom_emojis/images/000/017/094/static/05252745eb087806.png",
                "visible_in_picker" => true
              ]
        ];

        return new JsonResponse($data);
    }

    #[Route('/api/v1/timelines/home', name: 'api_timeline_home')]
    public function timelineHome(Request $request)
    {
        $data = [
        ];

        return new JsonResponse($data);
    }

    #[Route('/api/v1/timelines/public', name: 'api_timeline_public')]
    public function timelinePublic(Request $request)
    {
        $data = [
        ];

        return new JsonResponse($data);
    }

    #[Route('/api/v1/timelines/tag/{tag}', name: 'api_timeline_tag')]
    public function timelineTag(Request $request)
    {
        $data = [
        ];

        return new JsonResponse($data);
    }

    #[Route('/api/v1/markers', name: 'api_markers')]
    public function markers(Request $request)
    {
        $data = [
        ];

        return new JsonResponse($data);
    }

    #[Route('/api/v1/notifications', name: 'api_notifications')]
    public function notifications(Request $request)
    {
        $data = [
        ];

        return new JsonResponse($data);
    }

    #[Route('/api/v1/accounts/{id}', name: 'api_account')]
    public function account(string $id)
    {
        $uuid = Uuid::fromString($id);
        $account = $this->accountService->getAccountById($uuid);
        if (!$account) {
            throw new NotFoundHttpException();
        }

        return new JsonResponse($this->accountService->toJson($account));
    }

    #[Route('/api/v1/accounts/{id}/statuses', name: 'api_account_statuses')]
    public function statuses(string $id)
    {
        $data = [];

        return new JsonResponse($data);
    }

    #[Route('/api/v1/accounts/{acct}/following', name: 'api_account_following')]
    public function following(string $acct)
    {
        // Only local accounts are allowed
        if (str_contains($acct, '@')) {
            throw new NotFoundHttpException();
        }
        $account = $this->accountService->getAccount($acct);
        if (!$account) {
            throw new NotFoundHttpException();
        }

        $ret = [];
        foreach ($this->accountService->getFollowing($account) as $follower) {
            $ret[] = $this->accountService->toJson($follower);
        }

        return new JsonResponse($ret);
    }

    #[Route('/api/v1/accounts/{acct}/followers', name: 'api_account_followers')]
    public function followers(string $acct)
    {
        // Only local accounts are allowed
        if (str_contains($acct, '@')) {
            throw new NotFoundHttpException();
        }
        $account = $this->accountService->getAccount($acct);
        if (!$account) {
            throw new NotFoundHttpException();
        }


        $ret = [];
        foreach ($this->accountService->getFollowers($account) as $follower) {
            $ret[] = $this->accountService->toJson($follower);
        }

        return new JsonResponse($ret);
    }


    #[Route('/api/v1/apps', name: 'api_apps', methods: ['POST'])]
    public function apps(): Response
    {
//        return new NotFoundHttpException();

        $data = [
            'client_id' => 'DHPTID_' . strtoupper(bin2hex(random_bytes(32))),
            'client_secret' => 'DHPTSEC_' . strtoupper(bin2hex(random_bytes(32))),
        ];

        return new JsonResponse($data);
    }

    #[Route('/api/v1/instance', name: 'api_instance')]
    public function instance(): Response
    {
        $data = [
            'uri' => 'dhpt.nl',
            'title' => 'DonkeyHeads Mastodon Instance',
            'short_description' => 'Run by DonkeyHeads',
            'description' => 'Server written in PHP, so what could possibly go wrong!??',
            'email' => 'jthijssen@blafhoest.nl',
            'version' => '0.0.1',
//            'urls' => [
//                'streaming_api' => 'wss://mastodon.social'
//            ],
            'stats' => [
                'user_count' => 3463272,
                'status_count' => 56236324632,
                'domain_count' => 5235252,
            ],
            'thumbnail' => 'https://dhpt.nl/dh.jpg',
            'languages' => [
                'en'
            ],
            'registrations' => false,
            'approval_required' => true,
            'contact_account' => $this->accountService->getAccount(Config::ADMIN_USER),
        ];

        return new JsonResponse($data);
    }
}
