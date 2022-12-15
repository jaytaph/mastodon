<?php

namespace App\Repository;

use App\Entity\TagHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TagHistory>
 *
 * @method TagHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method TagHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method TagHistory[]    findAll()
 * @method TagHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TagHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TagHistory::class);
    }

    public function save(TagHistory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getTrendStats(\DateTime $since): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('uses', 'uses');
        $rsm->addScalarResult('accounts', 'accounts');
        $rsm->addScalarResult('name', 'name');
        $rsm->addScalarResult('date', 'date');

        $query = $this->getEntityManager()->createNativeQuery(
            'select sum(q1.count) as uses, count(q1.count) as accounts, q1.name, q1.date from (
             select count(account) as count, name, date, account
             from tag_history
             where date >= ?
             group by account, name, date
             order by count desc) as q1
            group by q1.name, q1.date;',
            $rsm
        );
        $query->setParameter(1, $since->format('Y-m-d'));

        return $query->getResult();
    }
}
