<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;
use Jaytaph\TypeArray\TypeArray;

class TagService
{
    protected EntityManagerInterface $doctrine;

    public function __construct(EntityManagerInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @param TypeArray $tagData
     * @return Tag
     */
    public function findOrCreateTag(TypeArray $tagData): Tag
    {
        $tag = $this->doctrine->getRepository(Tag::class)->findOneBy([
            'type' => $tagData->getString('[type]', ''),
            'name' => $tagData->getString('[name]', ''),
        ]);

        if (!$tag) {
            $tag = new Tag();
            $tag->setType($tagData->getString('[type]', ''));
            $tag->setName($tagData->getString('[name]', ''));
            $tag->setHref($tagData->getString('[href]', ''));
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
