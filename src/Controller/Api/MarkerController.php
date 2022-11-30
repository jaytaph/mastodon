<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\BaseApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MarkerController extends BaseApiController
{
    #[Route('/api/v1/markers', name: 'api_markers')]
    #[IsGranted('ROLE_OAUTH2_READ')]
    public function markers(): Response
    {
        $data = [
        ];

        return new JsonResponse($data);
    }
}
