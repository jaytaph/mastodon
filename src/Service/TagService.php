<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Tag;
use App\Entity\TagHistory;
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

    public function findOrCreateTag(TypeArray $tagData, \DateTime $dt, string $acct): Tag
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
        $this->doctrine->persist($tag);

        $history = new TagHistory();
        $history->setAccount($acct);
        $history->setName($tag->getName());
        $history->setDate($dt);
        $this->doctrine->persist($history);

        $this->doctrine->flush();

        $this->doctrine->getRepository(Tag::class)->increaseCount($tag);

        return $tag;
    }

    public function fetch(Uuid $id): ?Tag
    {
        return $this->doctrine->getRepository(Tag::class)->find($id);
    }

    public function getTrend(Tag $tag, \DateTime $since): TypeArray
    {
        /** @var string[][] $stats */
        $stats = $this->doctrine->getRepository(TagHistory::class)->getTrendStatsForTag($tag, $since);

        // If we cannot find any stats (because the tag is too old and not current anymore), we return empty stats
        if (!is_array($stats) || count($stats) === 0) {
            return TypeArray::empty();
        }

        // Return the first element of the array, which is the stats of the given tag
        return new TypeArray($stats[0]);
    }

    public function getTrends(\DateTime $since): TypeArray
    {
        /** @var string[][] $stats */
        $stats = $this->doctrine->getRepository(TagHistory::class)->getTrendStats($since);

        return new TypeArray($stats);
    }
}
