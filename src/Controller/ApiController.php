<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    #[Route('/api/v1/instance', name: 'api_instance')]
    public function index(): Response
    {
        $data = [
            'uri' => 'dhpt.nl',
            'title' => 'DonkeyHeads Mastodon Instance',
            'short_description' => 'Run by DonkeyHeads',
            'description' => 'Server written in PHP, so what could possibily go wrong!??',
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
            'contact_account' => [
                'id' => '1',
                'username' => 'jaytaph',
                'acct' => 'jaytaph',
                'display_name' => 'Joshua Thijssen',
                'locked' => false,
                'bot' => false,
                'created_at' => '2020-01-01T12:34:56.000Z',
                'note' => '<p>Prutser extraordinaire</p>',
                'url' => 'https://dhpt.nl/@jaytaph',
    //            'avatar' => 'https://files.mastodon.social/accounts/avatars/000/000/001/original/d96d39a0abb45b92.jpg',
    //            'avatar_static' => 'https://files.mastodon.social/accounts/avatars/000/000/001/original/d96d39a0abb45b92.jpg',
    //            'header' => 'https://files.mastodon.social/accounts/headers/000/000/001/original/c91b871f294ea63e.png',
    //     'header_static' => 'https://files.mastodon.social/accounts/headers/000/000/001/original/c91b871f294ea63e.png',
                 'followers_count' => 317112,
                'following_count' => 453,
                'statuses_count' => 60903,
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
            ]
        ];

        return new JsonResponse($data);
    }
}
