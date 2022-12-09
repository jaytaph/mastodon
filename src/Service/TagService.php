<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

class TagService
{
    protected EntityManagerInterface $doctrine;

    public function __construct(EntityManagerInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @param array<string,string|string[]> $tagData
     * @return Tag
     */
    public function findOrCreateTag(array $tagData): Tag
    {
        $tag = $this->doctrine->getRepository(Tag::class)->findOneBy(['type' => $tagData['type'], 'name' => $tagData['name']]);
        if (!$tag) {
            $tag = new Tag();
            /** @phpstan-ignore-next-line */
            $tag->setType($tagData['type']);
            /** @phpstan-ignore-next-line */
            $tag->setName($tagData['name']);
            /** @phpstan-ignore-next-line */
            $tag->setHref($tagData['href']);
            $tag->setCount(0);
        }

        // This is not the best way to track the count, but it's the easiest
        $tag->setCount($tag->getCount() + 1);

        $this->doctrine->persist($tag);
        $this->doctrine->flush();

        return $tag;
    }

    public function fetch(Uuid $id): ?Tag
    {
        return $this->doctrine->getRepository(Tag::class)->find($id);
    }
}
