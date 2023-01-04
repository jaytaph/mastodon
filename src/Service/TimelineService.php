<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Account;
use App\Entity\Status;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

class TimelineService
{
    protected EntityManagerInterface $doctrine;
    protected AccountService $accountService;
    protected ConfigService $configService;

    public function __construct(
        EntityManagerInterface $doctrine,
        AccountService $accountService,
        ConfigService $configService
    ) {
        $this->doctrine = $doctrine;
        $this->accountService = $accountService;
        $this->configService = $configService;
    }

    /**
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @return Status[]
     */
    public function getTimelineForAccount(
        Account $account,
        bool $local = true,
        bool $remote = false,
        bool $onlyMedia = false,
        string $maxId = '',
        string $sinceId = '',
        string $minId = '',
        int $limit = 40
    ): array {
        $qb = $this->doctrine->createQueryBuilder()
            ->select('s')
            ->from(Status::class, 's')
            ->where('s.owner = :owner')
            ->setParameter('owner', $account->getId())
            ->orderBy('s.createdAt', 'DESC')
            ->setMaxResults($limit);

        if ($local && !$remote) {
            $qb->andWhere('s.local = true');
        }
        if ($remote && !$local) {
            $qb->andWhere('s.local = false');
        }
        if ($onlyMedia) {
            $qb->andWhere('s.attachmentIds IS NOT NULL');
        }

        if ($minId !== '') {
            $minId = Uuid::fromBase58($minId);
            $status = $this->doctrine->getRepository(Status::class)->find($minId);
            if ($status) {
                $qb->andWhere('s.createdAt > :minCreatedAt')
                    ->setParameter('minCreatedAt', $status->getCreatedAt());
            }
        }
        if ($sinceId !== '') {
            $sinceId = Uuid::fromBase58($sinceId);
            $status = $this->doctrine->getRepository(Status::class)->find($sinceId);
            if ($status) {
                $qb->andWhere('s.createdAt > :sinceCreatedAt')
                    ->setParameter('sinceCreatedAt', $status->getCreatedAt());
            }
        }
        if ($maxId !== '') {
            $maxId = Uuid::fromBase58($maxId);
            $status = $this->doctrine->getRepository(Status::class)->find($maxId);
            if ($status) {
                $qb->andWhere('s.createdAt < :maxCreatedAt')
                    ->setParameter('maxCreatedAt', $status->getCreatedAt());
            }
        }

        $ret = [];
        $result = $qb->getQuery()->getResult();
        foreach ($result as $entry) {
            if (! $entry->getAccount()) {
                continue;
            }
            $ret[] = $entry;
        }

        return $ret;
    }
}
