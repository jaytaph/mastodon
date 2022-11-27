<?php

declare(strict_types=1);

namespace App\Service;

use App\ActivityPub;
use App\Entity\Account;
use App\Entity\Follower;
use App\Entity\Status;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Uid\Uuid;
use function Symfony\Component\DependencyInjection\Loader\Configurator\expr;

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
}
