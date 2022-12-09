<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\EmojiRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: EmojiRepository::class)]
class Emoji
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private Uuid $id;

    #[ORM\Column(length: 255)]
    private string $type = '';

    #[ORM\Column(length: 255)]
    private string $name = '';

    #[ORM\Column(length: 255)]
    private string $iconType = '';

    #[ORM\Column(length: 255)]
    private string $iconMediaType = '';

    #[ORM\Column(length: 255)]
    private string $iconUrl = '';

    #[ORM\Column(type: 'datetime', nullable: false)]
    private \DateTimeInterface $updatedAt;


    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getIconType(): ?string
    {
        return $this->iconType;
    }

    public function setIconType(string $iconType): self
    {
        $this->iconType = $iconType;

        return $this;
    }

    public function getIconMediaType(): ?string
    {
        return $this->iconMediaType;
    }

    public function setIconMediaType(string $iconMediaType): self
    {
        $this->iconMediaType = $iconMediaType;

        return $this;
    }

    public function getIconUrl(): ?string
    {
        return $this->iconUrl;
    }

    public function setIconUrl(string $iconUrl): self
    {
        $this->iconUrl = $iconUrl;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
