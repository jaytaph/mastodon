<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Emoji;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

class EmojiService
{
    protected EntityManagerInterface $doctrine;

    public function __construct(EntityManagerInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @param array<string,string|string[]> $data
     * @return Emoji
     */
    public function findOrCreateEmoji(array $data): Emoji
    {
        $emoji = $this->doctrine->getRepository(Emoji::class)->findOneBy(['name' => $data['name']]);
        if (!$emoji) {
            $emoji = new Emoji();
            /** @phpstan-ignore-next-line */
            $emoji->setType($data['type']);
            /** @phpstan-ignore-next-line */
            $emoji->setName($data['name']);
            /** @phpstan-ignore-next-line */
            $emoji->setIconType($data['icon']['type']);
            /** @phpstan-ignore-next-line */
            $emoji->setIconMediaType($data['icon']['mediaType']);
            /** @phpstan-ignore-next-line */
            $emoji->setIconUrl($data['icon']['url']);
            $emoji->setUpdatedAt(new \DateTimeImmutable());

            $this->doctrine->persist($emoji);
            $this->doctrine->flush();
        }

        return $emoji;
    }

    public function findEmojiById(Uuid $id): ?Emoji
    {
        return $this->doctrine->getRepository(Emoji::class)->find($id);
    }
}
