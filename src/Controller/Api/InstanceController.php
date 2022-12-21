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

        $data = [
            'uri' => $config->getInstanceDomain(),
            'title' => $config->getInstanceTitle(),
            'description' => $config->getInstanceDescription(),
            'short_description' => $config->getInstanceShortDescription(),
            'email' => $config->getInstanceEmail(),
            'version' => '4.0.1',       // @TODO: Store version in config somewhere
            'languages' => $config->getLanguages(),
            'registrations' => $config->isRegistrationAllowed(),
            'approval_required' => $config->isApprovalRequired(),
            'invites_enabled' => $config->isInviteEnabled(),
            'urls' => [
//                'streaming_api' => 'wss://' . $config->getInstanceDomain() . '/api/v1/streaming',
            ],
            'stats' => [
                'user_count' => $this->accountService->getLocalAccountCount(),
                'status_count' => $this->statusService->getLocalStatusCount(),
                'domain_count' => 1,        // @TODO: hardcoded
            ],
            'thumbnail' => $config->getThumbnailUrl(),
        ];

        $adminAccount = $this->accountService->findAccount($config->getAdminAccount());
        if ($adminAccount) {
            $data['contact_account'] = $this->accountService->toJson($adminAccount);
        }

        return new JsonResponse($data);
    }
}
