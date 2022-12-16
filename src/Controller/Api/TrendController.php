<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\BaseApiController;
use App\Service\TagService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TrendController extends BaseApiController
{
    #[Route('/api/v1/trends', name: 'api_trends')]
    #[IsGranted('ROLE_OAUTH2_READ')]
    public function trendHistory(TagService $tagService): Response
    {
        $since = (new \DateTime("now"))->sub(new \DateInterval('P1W'));
        $ret = $tagService->getTrends($since);

        return new JsonResponse($ret);
    }

    #[Route('/api/v1/trends/statuses', name: 'api_trends_statuses')]
    #[IsGranted('ROLE_OAUTH2_READ')]
    public function trendStatuses(): Response
    {
        return new JsonResponse([]);
    }

    #[Route('/api/v1/trends/tags', name: 'api_trends_tags')]
    #[IsGranted('ROLE_OAUTH2_READ')]
    public function trendTags(): Response
    {
        return new JsonResponse([]);
    }

    #[Route('/api/v1/trends/links', name: 'api_trends_links')]
    #[IsGranted('ROLE_OAUTH2_READ')]
    public function trendLinks(): Response
    {
        return new JsonResponse([]);
    }
}
