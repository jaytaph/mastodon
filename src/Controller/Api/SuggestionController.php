<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\BaseApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SuggestionController extends BaseApiController
{
    #[Route('/api/v2/suggestions', name: 'apiv2_suggestions')]
    #[IsGranted('ROLE_OAUTH2_READ')]
    public function trendStatuses(): Response
    {
        $account1 = $this->accountService->getAccount('jaytaph');
        $account2 = $this->accountService->getAccount('cybolic');

        $data = [
            [
                'source' => 'staff',
                'account' => $this->accountService->toJson($account1),
            ],
            [
                'source' => 'past-interactions',
                'account' => $this->accountService->toJson($account2),
            ]
        ];
        return new JsonResponse($data);
    }
}
