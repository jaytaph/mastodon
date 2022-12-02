<?php

declare(strict_types=1);

namespace App\Controller\Api\Timeline;

use App\Controller\BaseApiController;
use App\Entity\Account;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;

class TimelineController extends BaseApiController
{
    #[Route('/api/v1/timelines/home', name: 'api_timeline_home')]
    #[IsGranted('ROLE_OAUTH2_READ')]
    public function timelineHome(Request $request): Response
    {
        $account = $this->getOauthUser();

        $maxId = $request->query->getAlnum('max_id');
        $minId = $request->query->getAlnum('min_id');
        $sinceId = $request->query->getAlnum('since_id');
        $local = $request->query->getBoolean('local');
        $remote = $request->query->getBoolean('remote');
        $onlyMedia = $request->query->getBoolean('only_media');

        $timeline = $this->statusService->getTimelineForAccount($account, $local, $remote, $onlyMedia, $maxId, $minId, $sinceId);

        return new JsonResponse($timeline);
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
