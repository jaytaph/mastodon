<?php

declare(strict_types=1);

namespace App\ActivityStream;

class Collection
{
    protected array $elements = [];
    protected string $id;
    protected ?string $summary = null;

    public function __construct(string $id, array $elements, string $summary = null) {
        $this->id = $id;
        $this->elements = $elements;
        $this->summary = $summary;
    }

    public function addElement(array $element): void
    {
        $this->elements[] = $element;
    }

    public function getElements(): array
    {
        return $this->elements;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function toArray(): array
    {
        $data = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => $this->id,
            'type' => 'Collection',
            'totalItems' => count($this->elements),
            'items' => $this->elements,
        ];

        if ($this->summary) {
            $data['summary'] = $this->summary;
        }

        return $data;
    }

    public function getTotalItems(): int
    {
        return count($this->elements);
    }
}
