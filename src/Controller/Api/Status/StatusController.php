<?php

declare(strict_types=1);

namespace App\Controller\Api\Status;

use App\Controller\BaseApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StatusController extends BaseApiController
{
    #[Route('/api/v1/statuses', name: 'api_account_status_create')]
    #[IsGranted('ROLE_OAUTH2_WRITE')]
    public function createStatus(Request $request): Response
    {
        $account = $this->getOauthUser();
        $app = $this->accountService->getLoggedInApplication();

        $data = json_decode($request->getContent(), true);
        if (!is_array($data)) {
            $data = [];
        }
        $status = $this->statusService->createStatus($data, $account, $app);

        return new JsonResponse($this->statusService->toJson($status));
    }
}
