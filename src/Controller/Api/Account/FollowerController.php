<?php

declare(strict_types=1);

namespace App\Controller\Api\Account;

use App\Controller\BaseApiController;
use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

class FollowerController extends BaseApiController
{
    #[Route('/api/v1/accounts/{uuid}/follow', name: 'api_account_follow', methods: ['POST'])]
    #[IsGranted('ROLE_OAUTH2_FOLLOW')]
    public function follow(string $uuid): Response
    {
        $account = $this->accountService->findAccountById(Uuid::fromString($uuid));
        if (!$account) {
            return $this->json(['error' => 'Account not found'], Response::HTTP_NOT_FOUND);
        }

//        // @TODO: Implement follow logic
//        $reblogs = $request->request->getBoolean('reblogs', true);
//        $notify = $request->request->getBoolean('notify', false);
//        $language = $request->request->get('language', []);

        $this->accountService->follow($this->getOauthUser(), $account);

        // Reload account
        $account = $this->accountService->getAccountById(Uuid::fromString($uuid));

        $followsMe = $this->accountService->getFollowers($account);
        $followsMe = new ArrayCollection($followsMe);
        $meFollows = $this->accountService->getFollowing($account);
        $meFollows = new ArrayCollection($meFollows);

        $relationship = [
            'id' => $account->getId()->toBase58(),
            'acct' => $account->getAcct(),
            'following' => $meFollows->contains($account),
            'showing_reblogs' => true,
            'notifying' => true,
            'languages' => [ 'en' ],
            'followed_by' => $followsMe->contains($account),
            'blocking' => false,
            'blocked_by' => false,
            'muting' => false,
            'muting_notifications' => false,
            'requested' => false,
            'domain_blocking' => false,
            'endorsed' => false,
            'note' => '',
        ];

        return new JsonResponse($relationship);
    }

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
