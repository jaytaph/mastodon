<?php

namespace App\Entity;

use App\Repository\FollowerRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FollowerRepository::class)]
class Follower
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    #[ORM\Column(length: 255)]
    private string $user_id;

    #[ORM\Column(length: 255)]
    private string $follow_id;

    #[ORM\Column]
    private bool $accepted;

    public function getId(): string
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
