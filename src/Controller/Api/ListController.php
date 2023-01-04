<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\BaseApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ListController extends BaseApiController
{
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

        // @TODO: convert lists to api format through the model converter service

        return new JsonResponse($data);
    }
}
