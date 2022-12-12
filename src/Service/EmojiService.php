<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Emoji;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;
use Jaytaph\TypeArray\TypeArray;

class EmojiService
{
    protected EntityManagerInterface $doctrine;

    public function __construct(EntityManagerInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function findOrCreateEmoji(TypeArray $data): Emoji
    {
        $emoji = $this->doctrine->getRepository(Emoji::class)->findOneBy(['name' => $data->getString('[name]', '')]);
        if (!$emoji) {
            $emoji = new Emoji();
            $emoji->setType($data->getString('[type]', ''));
            $emoji->setName($data->getString('[name]', ''));
            $emoji->setIconType($data->getString('[icon][type]', ''));
            $emoji->setIconMediaType($data->getString('[icon][mediaType]', ''));
            $emoji->setIconUrl($data->getString('[icon][url]', ''));
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
