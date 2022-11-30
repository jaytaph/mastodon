<?php

declare(strict_types=1);

namespace App\Controller\Api\Timeline;

use App\Controller\BaseApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TimelineController extends BaseApiController
{
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

}
