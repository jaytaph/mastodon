<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\Tag as EntityTag;
use Symfony\Component\Uid\Uuid;

class Tag
{
    protected Uuid $id;
    protected string $type;
    protected string $name;
    protected string $href;
    protected int $count;

    public function __construct(Uuid $id, string $type, string $name, string $href, int $count)
    {
        $this->id = $id;
        $this->type = $type;
        $this->name = $name;
        $this->href = $href;
        $this->count = $count;
    }

    public static function fromEntity(EntityTag $entity): Tag
    {
        return new self(
            $entity->getId(),
            $entity->getType(),
            $entity->getName(),
            $entity->getHref(),
            $entity->getCount()
        );
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getHref(): string
    {
        return $this->href;
    }

    public function getCount(): int
    {
        return $this->count;
    }
}
