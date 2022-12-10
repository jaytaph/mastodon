<?php

declare(strict_types=1);

namespace App\Controller\Api\Status;

use App\Controller\BaseApiController;
use App\JsonArray;
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

        $data = JsonArray::fromJson($request->getContent());
        $status = $this->statusService->createStatus($data, $account, $app);

        return new JsonResponse($this->statusService->toJson($status));
    }

    #[Route('/api/v1/statuses/{uuid}/context', name: 'api_account_status_context')]
    #[IsGranted('ROLE_OAUTH2_READ')]
    public function context(string $uuid): Response
    {
        // $account = $this->getOauthUser();

        $status = $this->statusService->findStatusById($uuid);
        if (!$status) {
            throw $this->createNotFoundException();
        }

        if ($status->isPrivate()) {
            throw $this->createAccessDeniedException();
        }

        $ancestors = [];
        if ($status->getInReplyTo()) {
            $ancestors[] = $this->statusService->toJson($status->getInReplyTo());
        }


        $descendants = [];
        foreach ($this->statusService->getParents($status) as $parentStatus) {
            $descendants[] = $this->statusService->toJson($parentStatus);
        }


        return new JsonResponse([
            'ancestors' => $ancestors,
            'descendants' => $descendants,
        ]);
    }
}
