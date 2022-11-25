<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\FollowerRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: FollowerRepository::class)]
class Follower
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: Account::class, inversedBy: "followers")]
    #[ORM\JoinColumn(nullable: false)]
    private Account $user;

    #[ORM\ManyToOne(targetEntity: Account::class, inversedBy: "following")]
    #[ORM\JoinColumn(nullable: false)]
    private Account $follow;

    #[ORM\Column]
    private bool $accepted;

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function isAccepted(): bool
    {
        return $this->accepted;
    }

    public function setAccepted(bool $accepted): self
    {
        $this->accepted = $accepted;

        return $this;
    }

    public function getUser(): Account
    {
        return $this->user;
    }

    public function setUser(Account $user): void
    {
        $this->user = $user;
    }

    public function getFollow(): Account
    {
        return $this->follow;
    }

    public function setFollow(Account $follow): void
    {
        $this->follow = $follow;
    }
}
