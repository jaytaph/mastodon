<?php

declare(strict_types=1);

namespace App\Controller\Api\Account;

use App\Controller\BaseApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

class AccountController extends BaseApiController
{
    #[Route('/api/v1/accounts/{id}', name: 'api_account')]
    #[IsGranted('PUBLIC_ACCESS')]
    public function account(string $id): Response
    {
        $uuid = Uuid::fromString($id);
        $account = $this->accountService->findAccountById($uuid);
        if (!$account) {
            throw $this->createNotFoundException();
        }

        return new JsonResponse($this->accountService->toJson($account));
    }
}
