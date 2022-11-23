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
    #[ORM\Column]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private Uuid $id;

    #[ORM\Column(length: 255)]
    private string $user_id;

    #[ORM\Column(length: 255)]
    private string $follow_id;

    #[ORM\Column]
    private bool $accepted;

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getUserId(): string
    {
        return $this->user_id;
    }

    public function setUserId(string $user_id): Follower
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getFollowId(): string
    {
        return $this->follow_id;
    }

    public function setFollowId(string $follow_id): self
    {
        $this->follow_id = $follow_id;

        return $this;
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
}
