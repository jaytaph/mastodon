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
    private ?string $url = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $preview_url = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $text_url = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $remote_url = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $blurhash = null;

    #[ORM\Column]
    private array $meta = [];

    #[ORM\Column(length: 255)]
    private ?string $filename = null;

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
        return $this->preview_url;
    }

    public function setPreviewUrl(string $preview_url): self
    {
        $this->preview_url = $preview_url;

        return $this;
    }

    public function getTextUrl(): ?string
    {
        return $this->text_url;
    }

    public function setTextUrl(string $text_url): self
    {
        $this->text_url = $text_url;

        return $this;
    }

    public function getRemoteUrl(): ?string
    {
        return $this->remote_url;
    }

    public function setRemoteUrl(string $remote_url): self
    {
        $this->remote_url = $remote_url;

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

    /**
     * @return array|mixed[]
     */
    public function toArray(): array
    {
        $ret = [
            'id' => $this->getId(),
            'type' => $this->getType(),
            'url' => $this->getUrl(),
            'preview_url' => $this->getPreviewUrl(),
            'text_url' => $this->getTextUrl(),
            'remote_url' => $this->getRemoteUrl(),
            'description' => $this->getDescription(),
            'blurhash' => $this->getBlurhash(),
        ];

        if ($this->meta) {
            $ret['meta'] = $this->meta;
        }

        return $ret;
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
