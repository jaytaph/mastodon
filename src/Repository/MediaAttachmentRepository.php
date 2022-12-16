<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\MediaAttachment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MediaAttachment>
 *
 * @method MediaAttachment|null find($id, $lockMode = null, $lockVersion = null)
 * @method MediaAttachment|null findOneBy(array $criteria, array $orderBy = null)
 * @method MediaAttachment[]    findAll()
 * @method MediaAttachment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MediaAttachmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MediaAttachment::class);
    }

    public function save(MediaAttachment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MediaAttachment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
