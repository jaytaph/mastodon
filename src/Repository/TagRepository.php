<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tag>
 *
 * @method Tag|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tag|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tag[]    findAll()
 * @method Tag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }

    public function save(Tag $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Tag $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function increaseCount(Tag $tag): void
    {
        $em = $this->getEntityManager();
        $em->createQuery('UPDATE App\Entity\Tag t SET t.count = t.count + 1 WHERE t.id = :id')
            ->setParameter('id', $tag->getId())
            ->execute();
    }

    /**
     * @return Tag[]
     */
    public function search(string $query, int $offset, int $limit): array
    {
        $qb = $this->createQueryBuilder('t')
            ->where('LOWER(t.name) LIKE :q')
            ->setParameter('q', '%' . strtolower($query) . '%')
            ->orderBy('t.name', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }
}
