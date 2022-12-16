<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Account;
use App\Entity\Status;
use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;

class SearchService
{
    protected EntityManagerInterface $doctrine;
    protected WebfingerService $webfingerService;
    protected TagService $tagService;
    protected AccountService $accountService;
    protected StatusService $statusService;

    public function __construct(
        EntityManagerInterface $doctrine,
        WebfingerService $webfingerService,
        TagService $tagService,
        AccountService $accountService,
        StatusService $statusService
    ) {
        $this->doctrine = $doctrine;
        $this->webfingerService = $webfingerService;
        $this->tagService = $tagService;
        $this->accountService = $accountService;
        $this->statusService = $statusService;
    }

    /**
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @return mixed[]
     */
    public function search(
        ?Account $source,
        string $query,
        ?string $type,
        bool $resolve,
        ?string $accountId,
        ?string $minId,
        ?string $maxId,
        int $offset,
        int $limit
    ): array {
        if ($limit <= 0) {
            $limit = 20;
        }
        if ($limit > 40) {
            $limit = 40;
        }
        if ($offset < 0) {
            $offset = 0;
        }

        $ret = [
            'accounts' => [],
            'statuses' => [],
            'hashtags' => [],
        ];

        // Search accounts
        if ($type === null || $type == 'accounts') {
            $foundResolved = 0;
            if ($resolve && $source) {
                $account = $this->webfingerService->fetch($source, $query);
                if ($account) {
                    $ret['accounts'][] = $this->accountService->toJson($account);
                }
                $foundResolved  = 1;
            }

            $items = $this->doctrine->getRepository(Account::class)->search($query, $offset, $limit - $foundResolved);
            foreach ($items as $item) {
                $ret['accounts'][] = $this->accountService->toJson($item);
            }
        }

        // Search hashtags
        if ($type === null || $type == 'hashtags') {
            $since = (new \DateTime("now"))->sub(new \DateInterval("P1W"));

            $items = $this->doctrine->getRepository(Tag::class)->search($query, $offset, $limit);
            foreach ($items as $item) {
                $stats = $this->tagService->getTrend($item, $since);
                if (! $stats->isEmpty()) {
                    $ret['hashtags'][] = $stats->toArray();
                }
            }
        }

        // Search statuses
        if ($type === null || $type == 'statuses') {
            $account = null;
            if ($accountId) {
                $account = $this->doctrine->getRepository(Account::class)->find($accountId);
            }

            $items = $this->doctrine->getRepository(Status::class)->search($query, $offset, $limit, $minId, $maxId, $account);
            foreach ($items as $item) {
                $ret['statuses'][] = $this->statusService->toJson($item);
            }
        }

        return $ret;
    }
}
