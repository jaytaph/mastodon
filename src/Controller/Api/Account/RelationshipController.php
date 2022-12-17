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
use Symfony\Component\Uid\Uuid;

class RelationshipController extends BaseApiController
{
    #[Route('/api/v1/accounts/relationships', name: 'api_account_relationship', priority: 10)]
    #[IsGranted('ROLE_OAUTH2_FOLLOW')]
    public function relationships(Request $request): Response
    {
        $ids = $request->get('id');
        if (! is_array($ids)) {
            $ids = [$ids];
        }

        $ret = [];
        $account = $this->getOauthUser();

        // People who are following the user
        $followsMe = $this->accountService->getFollowers($account);
        $followsMe = new ArrayCollection($followsMe);

        // People who are followed by the user
        $meFollows = $this->accountService->getFollowing($account);
        $meFollows = new ArrayCollection($meFollows);

        foreach ($ids as $id) {
            if (is_string($id)) {
                $id = Uuid::fromString($id);
            }

            /** @var Uuid $id */
            $checkAccount = $this->accountService->findAccountById($id);
            if (!$checkAccount) {
                continue;
            }

            $ret[$checkAccount->getId()->toBase58()] = [
                'id' => $checkAccount->getId()->toBase58(),
                'acct' => $checkAccount->getAcct(),
                'following' => $meFollows->contains($checkAccount),
                'showing_reblogs' => true,
                'notifying' => true,
                'languages' => [ 'en' ],
                'followed_by' => $followsMe->contains($checkAccount),
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
