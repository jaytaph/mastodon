<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    #[Route('/api/v1/accounts/verify_credentials', name: 'api_verify_credentials')]
    public function verifyCredentials(Request $request)
    {
        $data = $this->getAccount(1);

        return new JsonResponse($data);
    }

    #[Route('/api/v1/accounts/{id}', name: 'api_account')]
    public function account(string $id)
    {
        $data = $this->getAccount($id);

        return new JsonResponse($data);
    }

    #[Route('/api/v1/accounts/{id}/statuses', name: 'api_account_statuses')]
    public function statuses(string $id)
    {
        $data = [];

        return new JsonResponse($data);
    }

    #[Route('/api/v1/accounts/{id}/following', name: 'api_account_following')]
    public function following(string $id)
    {
        $data = [
            [
                'id' => '1234',
                'username' => 'Skoop',
                'acct' => 'skoop@phpc.social',
                'display_name' => 'Skoop',
            ],
            [
                'id' => '1235',
                'username' => 'JayTest',
                'acct' => 'jaytest@mastodon.nl',
                'display_name' => 'Jay Test',
            ]

        ];

//        $data = [
//            '@context' => 'https://www.w3.org/ns/activitystreams',
//            'id' => "https://phpt.nl/users/jaytaph/following",
//            'type' => 'OrderedCollection',
//            'totalItems' => 5,
//            'orderedItems' => [
//                'https://mastodon.nl/users/jaytest',
//                'https://phpc.social/users/andrewfeeney',
//                'https://phpc.social/users/shochdoerfer',
//                'https://phpc.social/users/Skoop',
//                'https://toet.dnzm.nl/users/max',
//            ]
//        ];

        return new JsonResponse($data);
    }
    #[Route('/api/v1/accounts/{id}/followers', name: 'api_account_followers')]
    public function followers(string $id)
    {
        $data = [
            [
                'id' => '1234',
                'username' => 'Skoop',
                'acct' => 'skoop@phpc.social',
                'display_name' => 'Skoop',
            ]
        ];

//        $data = [
//            '@context' => 'https://www.w3.org/ns/activitystreams',
//            'id' => "https://phpt.nl/users/jaytaph/followers",
//            'type' => 'OrderedCollection',
//            'totalItems' => 3,
//            'orderedItems' => [
//                'https://mastodon.nl/users/jaytest',
//                'https://phpc.social/users/Skoop',
//                'https://toet.dnzm.nl/users/max',
//            ]
//        ];

        return new JsonResponse($data);
    }


    #[Route('/api/v1/apps', name: 'api_apps', methods: ['POST'])]
    public function apps(): Response
    {
//        return new NotFoundHttpException();

        $data = [
            'client_id' => 'DHPTID_' . bin2hex(random_bytes(32)),
            'client_secret' => 'DHPTSEC_' . bin2hex(random_bytes(32)),
        ];

        return new JsonResponse($data);
    }

    #[Route('/oauth/authorize', name: 'oauth_authorize', methods: ['GET'])]
    public function authorize(Request $request): Response
    {
        // Just approve the thing....



        $url = $request->get('redirect_uri');
        $url .= '?code=' . bin2hex(random_bytes(32));

        return new RedirectResponse($url);
    }

    #[Route('/oauth/token', name: 'oauth_token', methods: ['POST'])]
    public function token(Request $request): Response
    {
        $data = [
            'access_token' => 'DHPTACC_' . bin2hex(random_bytes(32)),
            'token_type' => 'bearer',
            'expires_in' => 3600,
            'refresh_token' => 'DHPTREF_' . bin2hex(random_bytes(32)),
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
            'contact_account' => $this->getAccount(1),
        ];

        return new JsonResponse($data);
    }

    protected function getAccount(string $id): array
    {
        return [
            'id' => '1',
            'username' => 'jaytaph',
            'acct' => 'jaytaph',
            'display_name' => 'Joshua Thijssen',
            'locked' => false,
            'bot' => false,
            'created_at' => '2020-01-01T12:34:56.000Z',
            'note' => '<p>Prutser extraordinaire</p>',
            'url' => 'https://dhpt.nl/@jaytaph',
                'avatar' => 'https://dhpt.nl/dh.jpg',
        //            'avatar_static' => 'https://files.mastodon.social/accounts/avatars/000/000/001/original/d96d39a0abb45b92.jpg',
        //            'header' => 'https://files.mastodon.social/accounts/headers/000/000/001/original/c91b871f294ea63e.png',
        //     'header_static' => 'https://files.mastodon.social/accounts/headers/000/000/001/original/c91b871f294ea63e.png',
             'followers_count' => 1,
            'following_count' => 2,
            'statuses_count' => 123,
            'last_status_at' => '2019-11-26T21:14:44.522Z',
            'emojis' => [],
            'fields' => [
                [
                    'name' => 'Homepage',
                    'value' => '<a href=\'https://dhpt.nl\' rel=\'me nofollow noopener noreferrer\' target=\'_blank\'><span class=\'invisible\'>https://</span><span class=\'\'>dhpt.nl</span><span class=\'invisible\'></span></a>',
                    'verified_at' => '2019-07-15T18:29:57.191+00:00'
                ],
                [
                    'name' => 'Blogue',
                    'value' => '<a href=\'https://adayinthelifeof.nl\' rel=\'me nofollow noopener noreferrer\' target=\'_blank\'><span class=\'invisible\'>https://</span><span class=\'\'>adayinthelifeof.nl</span><span class=\'invisible\'></span></a>',
                    'verified_at' => '2019-07-15T18:29:57.191+00:00'
                ],
            ],
        ];
    }
}
