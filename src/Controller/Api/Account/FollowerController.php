<?php

declare(strict_types=1);

namespace App\Controller\Api\Account;

use App\Controller\BaseApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FollowerController extends BaseApiController
{
    #[Route('/api/v1/accounts/{uuid}/following', name: 'api_account_following')]
    #[IsGranted('ROLE_OAUTH2_READ')]
    public function following(string $uuid): Response
    {
        $account = $this->findAccountById($uuid);
        if (!$account) {
            throw $this->createNotFoundException();
        }

        $ret = [];
        foreach ($this->accountService->getFollowing($account) as $follower) {
            $ret[] = $this->accountService->toJson($follower);
        }

        return new JsonResponse($ret);
    }

    #[Route('/api/v1/accounts/{uuid}/followers', name: 'api_account_followers')]
    #[IsGranted('ROLE_OAUTH2_READ')]
    public function followers(string $uuid): Response
    {
        $account = $this->findAccountById($uuid);
        if (!$account) {
            throw $this->createNotFoundException();
        }

        $ret = [];
        foreach ($this->accountService->getFollowers($account) as $follower) {
            $ret[] = $this->accountService->toJson($follower);
        }

        return new JsonResponse($ret);
    }

    #[Route('/api/v1/follow_requests', name: 'api_follow_requests')]
    #[IsGranted('ROLE_OAUTH2_READ')]
    #[IsGranted('ROLE_OAUTH2_FOLLOW')]
    public function followRequests(): Response
    {
        $data = [
        ];

        return new JsonResponse($data);
    }
}
