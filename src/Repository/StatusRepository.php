<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Account;
use App\Entity\Status;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<Status>
 *
 * @method Status|null find($id, $lockMode = null, $lockVersion = null)
 * @method Status|null findOneBy(array $criteria, array $orderBy = null)
 * @method Status[]    findAll()
 * @method Status[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Status::class);
    }

    public function save(Status $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Status $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Status[]
     */
    public function search(string $query, int $offset, int $limit, ?string $minId, ?string $maxId, ?Account $account): array
    {
        $qb = $this->createQueryBuilder('s')
            ->where('s.content LIKE :q')
            ->setParameter('q', '%' . $query . '%')
            ->orderBy('s.id', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        if ($minId) {
            $minId = Uuid::fromBase58($minId);
            $status = $this->find($minId);
            if ($status) {
                $qb->andWhere('s.createdAt > :minCreatedAt')
                    ->setParameter('minCreatedAt', $status->getCreatedAt());
            }
        }
        if ($maxId) {
            $maxId = Uuid::fromBase58($maxId);
            $status = $this->find($maxId);
            if ($status) {
                $qb->andWhere('s.createdAt < :maxCreatedAt')
                    ->setParameter('maxCreatedAt', $status->getCreatedAt());
            }
        }

        if ($account) {
            $qb->andWhere('s.account = :account')
                ->setParameter('account', $account->getId());
        }

        /** @var Status[] $ret */
        $ret = $qb->getQuery()->getResult();
        return $ret;
    }
}
