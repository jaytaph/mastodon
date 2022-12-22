<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\BaseApiController;
use App\Service\SearchService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends BaseApiController
{
    #[Route('/api/v2/search', name: 'api_search')]
    public function search(Request $request, SearchService $searchService): Response
    {
        if ($this->getUser() === null && ($request->query->has('resolve') || $request->query->has('offset'))) {
            return new JsonResponse([
                'error' => 'This endpoint does not support the resolve or offset parameters for public access',
            ], 400);
        }

        if ($this->getUser() !== null && !$this->isGranted('ROLE_OAUTH2_READ')) {
            return new JsonResponse([
                'error' => 'This endpoint requires the read scope',
            ], 400);
        }

        $query = $request->query->get('q', '');
        $resolve = $request->query->getBoolean('resolve', false);
        $type = $request->query->get('type');
        $accountId = $request->query->get('account_id');
        $maxId = $request->query->get('max_id');
        $minId = $request->query->get('min_id');
        $limit = $request->query->getInt('limit', 20);
        $offset = $request->query->getInt('offset');

        $ret = $searchService->search(
            source: $this->getOauthAccount(),
            query: $query,
            type: $type,
            resolve: $resolve,
            accountId: $accountId,
            minId: $minId,
            maxId: $maxId,
            offset: $offset,
            limit: $limit
        );

        return new JsonResponse($ret);
    }
}
