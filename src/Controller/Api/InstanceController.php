<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\BaseApiController;
use App\Service\ConfigService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InstanceController extends BaseApiController
{
    #[Route('/api/v1/instance', name: 'api_instance')]
    #[IsGranted('PUBLIC_ACCESS')]
    public function instance(ConfigService $config): Response
    {
        $config = $config->getConfig();

        $data = $this->apiModelConverter->config($config);
        return new JsonResponse($data);
    }
}
