<?php

declare(strict_types=1);

namespace App\Controller\Api\Account;

use App\Controller\BaseApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccountController extends BaseApiController
{
    #[Route('/api/v1/accounts/{uuid}', name: 'api_account')]
    #[IsGranted('PUBLIC_ACCESS')]
    public function account(string $uuid): Response
    {
        $account = $this->findAccountById($uuid);

        return new JsonResponse($this->accountService->toJson($account));
    }
}
