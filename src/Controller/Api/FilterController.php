<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\BaseApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FilterController extends BaseApiController
{
    #[Route('/api/v1/filters', name: 'api_filters')]
    #[IsGranted('ROLE_OAUTH2_READ')]
    public function filters(): Response
    {
        $data = [
        ];

        return new JsonResponse($data);
    }

}
