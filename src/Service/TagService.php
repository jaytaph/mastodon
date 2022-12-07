<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;

class TagService
{
    protected EntityManagerInterface $doctrine;

    public function __construct(EntityManagerInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function findOrCreateTag(mixed $tagData): Tag
    {
        $tag = $this->doctrine->getRepository(Tag::class)->findOneBy(['type' => $tagData['type'], 'name' => $tagData['name']]);
        if (!$tag) {
            $tag = new Tag();
            $tag->setType($tagData['type']);
            $tag->setName($tagData['name']);
            $tag->setHref($tagData['href']);
            $tag->setCount(0);
        }

        // This is not the best way to track the count, but it's the easiest
        $tag->setCount($tag->getCount() + 1);

        $this->doctrine->persist($tag);
        $this->doctrine->flush();

        return $tag;
    }
}
