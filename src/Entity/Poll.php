<?php

namespace App\Entity;

use App\Repository\PollRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: PollRepository::class)]
class Poll
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private Uuid $id;

    #[ORM\Column]
    private \DateTimeImmutable $expiresAt;

    #[ORM\Column]
    private bool $expired;

    #[ORM\Column]
    private bool $multiple;

    #[ORM\Column]
    private int $votesCount = 0;

    #[ORM\Column]
    private int $votersCount = 0;

    #[ORM\Column]
    private array $options = [];

    #[ORM\Column]
    private array $emojis = [];

    #[ORM\Column]
    private array $votes = [];

    #[ORM\Column]
    private array $ownVotes = [];

    #[ORM\OneToOne(inversedBy: 'poll', cascade: ['persist', 'remove'])]
    private ?Status $status = null;

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getExpiresAt(): \DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(\DateTimeImmutable $expiresAt): self
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function isExpired(): bool
    {
        return $this->expired;
    }

    public function setExpired(bool $expired): self
    {
        $this->expired = $expired;

        return $this;
    }

    public function isMultiple(): bool
    {
        return $this->multiple;
    }

    public function setMultiple(bool $multiple): self
    {
        $this->multiple = $multiple;

        return $this;
    }

    public function getVotesCount(): int
    {
        return $this->votesCount;
    }

    public function setVotesCount(int $votesCount): self
    {
        $this->votesCount = $votesCount;

        return $this;
    }

    public function getVotersCount(): int
    {
        return $this->votersCount;
    }

    public function setVotersCount(int $votersCount): self
    {
        $this->votersCount = $votersCount;

        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function getEmojis(): array
    {
        return $this->emojis;
    }

    public function setEmojis(array $emojis): self
    {
        $this->emojis = $emojis;

        return $this;
    }

    public function getVotes(): array
    {
        return $this->votes;
    }

    public function setVotes(array $votes): self
    {
        $this->votes = $votes;

        return $this;
    }

    public function getOwnVotes(): array
    {
        return $this->ownVotes;
    }

    public function setOwnVotes(array $ownVotes): self
    {
        $this->ownVotes = $ownVotes;

        return $this;
    }

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(?Status $status): self
    {
        $this->status = $status;

        return $this;
    }
}
