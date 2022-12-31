<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\DbQueueEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DbQueueEntry>
 *
 * @method DbQueueEntry|null find($id, $lockMode = null, $lockVersion = null)
 * @method DbQueueEntry|null findOneBy(array $criteria, array $orderBy = null)
 * @method DbQueueEntry[]    findAll()
 * @method DbQueueEntry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DbQueueEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DbQueueEntry::class);
    }

    public function save(DbQueueEntry $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(DbQueueEntry $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
