<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\MediaAttachmentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: MediaAttachmentRepository::class)]
class MediaAttachment
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private Uuid $id;

    #[ORM\Column(length: 255)]
    private string $type = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $url;

    #[ORM\Column(type: Types::TEXT)]
    private string $previewUrl;

    #[ORM\Column(type: Types::TEXT)]
    private string $textUrl;

    #[ORM\Column(type: Types::TEXT)]
    private string $remoteUrl;

    #[ORM\Column(type: Types::TEXT)]
    private string $description;

    #[ORM\Column(length: 255)]
    private string $blurhash;

    /** @var mixed[] */
    #[ORM\Column]
    private array $meta = [];

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $filename = null;

    /** @var mixed[] */
    #[ORM\Column]
    private array $focus = [];

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

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getPreviewUrl(): ?string
    {
        return $this->previewUrl;
    }

    public function setPreviewUrl(string $previewUrl): self
    {
        $this->previewUrl = $previewUrl;

        return $this;
    }

    public function getTextUrl(): ?string
    {
        return $this->textUrl;
    }

    public function setTextUrl(string $textUrl): self
    {
        $this->textUrl = $textUrl;

        return $this;
    }

    public function getRemoteUrl(): ?string
    {
        return $this->remoteUrl;
    }

    public function setRemoteUrl(string $remoteUrl): self
    {
        $this->remoteUrl = $remoteUrl;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getBlurhash(): ?string
    {
        return $this->blurhash;
    }

    public function setBlurhash(string $blurhash): self
    {
        $this->blurhash = $blurhash;

        return $this;
    }

    /**
     * @return array|mixed[]
     */
    public function getMeta(): array
    {
        return $this->meta;
    }

    /**
     * @param array|mixed[] $meta
     * @return $this
     */
    public function setMeta(array $meta): self
    {
        $this->meta = $meta;

        return $this;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * @return array|mixed[]
     */
    public function getFocus(): array
    {
        return $this->focus;
    }

    /**
     * @param array|mixed[] $focus
     * @return $this
     */
    public function setFocus(array $focus): self
    {
        $this->focus = $focus;

        return $this;
    }
}
