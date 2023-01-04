<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\BaseApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EmojiController extends BaseApiController
{
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

        // @TODO: convert emojis to API format via model converter service

        return new JsonResponse($data);
    }
}
