<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Emoji;
use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;

class EmojiService
{
    protected EntityManagerInterface $doctrine;

    public function __construct(EntityManagerInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function findOrCreateEmoji(array $data): Emoji
    {
        $emoji = $this->doctrine->getRepository(Emoji::class)->findOneBy(['name' => $data['name']]);
        if (!$emoji) {
            $emoji = new Emoji();
            $emoji->setType($data['type']);
            $emoji->setName($data['name']);
            $emoji->setIconType($data['icon']['type']);
            $emoji->setIconMediaType($data['icon']['mediaType']);
            $emoji->setIconUrl($data['icon']['url']);
            $emoji->setUpdatedAt(new \DateTimeImmutable());

            $this->doctrine->persist($emoji);
            $this->doctrine->flush();
        }

        return $emoji;
    }

    public function findEmojiById(mixed $id): ?Emoji
    {
        return $this->doctrine->getRepository(Emoji::class)->find($id);
    }
}
