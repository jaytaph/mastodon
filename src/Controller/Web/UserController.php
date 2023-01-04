<?php

declare(strict_types=1);

namespace App\Controller\Web;

use App\ActivityStream\Collection;
use App\ActivityStream\CollectionPage;
use App\Controller\AccountTrait;
use App\Entity\Account;
use App\Service\AccountService;
use App\Service\TimelineService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    use AccountTrait;

    protected AccountService $accountService;
    protected TimelineService $timelineService;

    public function __construct(AccountService $accountService, TimelineService $timelineService)
    {
        $this->accountService = $accountService;
        $this->timelineService = $timelineService;
    }

    #[Cache(vary: ['Accept'])]
    #[Route('/@{acct}', name: 'app_users_show_profile')]
    public function profile(Request $request, string $acct): Response
    {
        $account = $this->findAccount($acct, localOnly: true);
        if (!$account) {
            throw $this->createNotFoundException();
        }

        if ($request->getPreferredFormat() == 'json') {
            return new JsonResponse($this->accountService->toProfileJson($account));
        }

        return $this->render('user/show.html.twig', [
            'account' => $account,
        ]);
    }

    #[Cache(vary: ['Accept'])]
    #[Route('/users/{acct}', name: 'app_users_show')]
    public function user(Request $request, string $acct): Response
    {
        $account = $this->findAccount($acct, localOnly: true);
        if (!$account) {
            throw $this->createNotFoundException();
        }

        if ($request->getPreferredFormat() == 'json') {
            return new JsonResponse($this->accountService->toProfileJson($account));
        }

        $timeline = $this->timelineService->getTimelineForAccount($account, local: true, remote: true, onlyMedia: false, limit: 40);

        return $this->render('user/show.html.twig', [
            'account' => $account,
            'timeline' => $timeline,
        ]);
    }

    #[Route('/users/{acct}/followers', name: 'app_users_followers')]
    public function followers(Request $request, string $acct): Response
    {
        $account = $this->findAccount($acct, localOnly: true);
        if (!$account) {
            throw $this->createNotFoundException();
        }

        $elements = array_map(function (Account $follower) {
            return $follower->getUri();
        }, $this->accountService->getFollowers($account));

        $collection = new Collection($account->getUri() . '/followers', $elements);
        $data = $collection->toArray();

        if ($collection->getTotalItems() > 50) {
            $page = new CollectionPage($collection);
            $data = $page->getPage($request->query->getInt('page', 1), 20);
        }

        return new JsonResponse($data);
    }

    #[Route('/users/{acct}/following', name: 'app_users_followings')]
    public function followings(Request $request, string $acct): Response
    {
        $account = $this->findAccount($acct, localOnly: true);
        if (!$account) {
            throw $this->createNotFoundException();
        }

        $elements = array_map(function (Account $follower) {
            return $follower->getUri();
        }, $this->accountService->getFollowing($account));

        $collection = new Collection($account->getUri() . '/followings', $elements);
        $data = $collection->toArray();

        if ($collection->getTotalItems() > 50) {
            $page = new CollectionPage($collection);
            $data = $page->getPage($request->query->getInt('page', 1), 20);
        }

        return new JsonResponse($data);
    }
}
