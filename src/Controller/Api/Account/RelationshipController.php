<?php

declare(strict_types=1);

namespace App\Controller\Api\Account;

use App\Controller\BaseApiController;
use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RelationshipController extends BaseApiController
{
    #[Route('/api/v1/accounts/relationships', name: 'api_account_relationship', priority: 10)]
    #[IsGranted('ROLE_OAUTH2_FOLLOW')]
    public function relationships(Request $request): Response
    {
        $user = $this->getUser();
        if (is_null($user)) {
            throw $this->createNotFoundException();
        }

        $account = $this->accountService->findAccount($user->getUserIdentifier());
        if (!$account) {
            throw $this->createNotFoundException();
        }

        $ret = [];

        // People who are following the user
        $followsMe = $this->accountService->getFollowers($account);
        $followsMe = new ArrayCollection($followsMe);

        // People who are followed by the user
        $meFollows = $this->accountService->getFollowing($account);
        $meFollows = new ArrayCollection($meFollows);

        // People who the user follows
        foreach ($meFollows as $follower) {
            $ret[$follower->getId()->toBase58()] = [
                'id' => $follower->getId()->toBase58(),
                'following' => true,
                'showing_reblogs' => true,
                'notifying' => true,
                'languages' => [ 'en' ],
                'followed_by' => $followsMe->contains($follower),
                'blocking' => false,
                'blocked_by' => false,
                'muting' => false,
                'muting_notifications' => false,
                'requested' => false,
                'domain_blocking' => false,
                'endorsed' => false,
                'note' => '',
            ];
        }

        // People who are followed by the user
        foreach ($followsMe as $follower) {
            $ret[$follower->getId()->toBase58()] = [
                'id' => $follower->getId()->toBase58(),
                'following' => $meFollows->contains($follower),
                'showing_reblogs' => true,
                'notifying' => true,
                'languages' => [ 'en' ],
                'followed_by' => true,
                'blocking' => false,
                'blocked_by' => false,
                'muting' => false,
                'muting_notifications' => false,
                'requested' => false,
                'domain_blocking' => false,
                'endorsed' => false,
                'note' => '',
            ];
        }

        return new JsonResponse(array_values($ret));
    }
}
