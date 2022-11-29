<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Status;
use Doctrine\ORM\EntityManagerInterface;

class StatusService
{
    protected EntityManagerInterface $doctrine;

    public function __construct(EntityManagerInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function getLocalStatusCount(): int
    {
        return $this->doctrine->getRepository(Status::class)->count(['local' => true]);
    }

    public function findStatusByURI(string $uri): ?Status
    {
        return $this->doctrine->getRepository(Status::class)->findOneby(['uri' => $uri]);
    }
}
