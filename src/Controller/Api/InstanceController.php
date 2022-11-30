<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Config;
use App\Controller\BaseApiController;
use Doctrine\ORM\EntityNotFoundException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InstanceController extends BaseApiController
{
    /**
     * @throws EntityNotFoundException
     */
    #[Route('/api/v1/instance', name: 'api_instance')]
    #[IsGranted('PUBLIC_ACCESS')]
    public function instance(): Response
    {
        $adminAccount = $this->accountService->getAccount(Config::ADMIN_USER);

        $data = [
            'uri' => Config::SITE_DOMAIN,
            'title' => 'DonkeyHeads Mastodon Instance',
            'description' => 'Server written in PHP, so what could possibly go wrong!??',
            'short_description' => 'Run by DonkeyHeads',
            'email' => 'jthijssen@blafhoest.nl',
            'version' => '4.0.1',
            'languages' => [
                'en',
                'nl',
            ],
            'registrations' => false,
            'approval_required' => true,
            'invites_enabled' => true,
            'urls' => [
                'streaming_api' => 'wss://' . Config::SITE_DOMAIN . '/api/v1/streaming',
            ],
            'stats' => [
                'user_count' => $this->accountService->getLocalAccountCount(),
                'status_count' => $this->statusService->getLocalStatusCount(),
                'domain_count' => 1,
            ],
            'thumbnail' => 'https://dhpt.nl/dh.jpg',
            'contact_account' => $this->accountService->toJson($adminAccount),
        ];

        return new JsonResponse($data);
    }
}
