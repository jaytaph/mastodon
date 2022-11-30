<?php

declare(strict_types=1);

namespace App\Controller\Api\Account;

use App\Controller\BaseApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VerifyController extends BaseApiController
{
    #[Route('/api/v1/accounts/verify_credentials', name: 'api_verify_credentials')]
    #[IsGranted('ROLE_OAUTH2_READ')]
    public function verifyCredentials(): Response
    {
        $user = $this->getUser();
        if (is_null($user)) {
            throw $this->createNotFoundException();
        }

        $account = $this->accountService->findAccount($user->getUserIdentifier());
        if (!$account) {
            throw $this->createNotFoundException();
        }

        return new JsonResponse($this->accountService->toJson($account));
    }
}
