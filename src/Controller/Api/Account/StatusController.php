<?php

declare(strict_types=1);

namespace App\Controller\Api\Account;

use App\Controller\BaseApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

class StatusController extends BaseApiController
{
    #[Route('/api/v1/accounts/{uuid}/statuses', name: 'api_account_statuses')]
    #[IsGranted('PUBLIC_ACCESS')]
    #[IsGranted('ROLE_OAUTH2_READ')]
    public function statuses(string $uuid): Response
    {
        $account = $this->accountService->findAccountById(Uuid::fromString($uuid));
        if (!$account) {
            throw $this->createNotFoundException();
        }

        // Only return public statuses when we are not logged in
//        $publicOnly = !$this->isGranted('ROLE_OAUTH2_READ');

        $data = [];

        return new JsonResponse($data);
    }
}
