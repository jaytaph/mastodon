<?php

declare(strict_types=1);

namespace App\Controller\Api\Timeline;

use App\Controller\BaseApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TagController extends BaseApiController
{
    #[Route('/api/v1/timelines/tag/{tag}', name: 'api_timeline_tag')]
    #[IsGranted('ROLE_OAUTH2_READ')]
    public function timelineTag(): Response
    {
        $data = [
        ];

        return new JsonResponse($data);
    }
}
