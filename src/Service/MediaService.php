<?php

declare(strict_types=1);

namespace App\Service;

use App\ActivityPub;
use App\Config;
use App\Entity\Account;
use App\Entity\Follower;
use App\Entity\MediaAttachment;
use App\Entity\Status;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use kornrunner\Blurhash\Blurhash;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Uid\Uuid;
use function Symfony\Component\DependencyInjection\Loader\Configurator\expr;

class MediaService
{
    protected EntityManagerInterface $doctrine;
    protected string $imagePath;

    public function __construct(EntityManagerInterface $doctrine, string $imagePath)
    {
        $this->doctrine = $doctrine;
        $this->imagePath = $imagePath;
    }

    public function findMediaAttachmentById(Uuid $uuid): ?MediaAttachment
    {
        return $this->doctrine->getRepository(MediaAttachment::class)->find($uuid);
    }

    public function save(MediaAttachment $mediaAttachment): void
    {
        $this->doctrine->persist($mediaAttachment);
        $this->doctrine->flush();
    }

    public function createMediaAttachment(UploadedFile $file): MediaAttachment
    {
        // Persist so we can get an ID
        $mediaAttachment = new MediaAttachment();
        $this->doctrine->persist($mediaAttachment);

        $id = $mediaAttachment->getId();
        $filename = $id . '.' . $file->guessExtension();
//        $imageData = $file->openFile()->fread($file->getSize());
        $file->move($this->imagePath, $filename);

        $mediaAttachment->setFilename($filename);
        $mediaAttachment->setType('image');
        $mediaAttachment->setDescription('Uploaded via API');
        $mediaAttachment->setUrl(Config::SITE_URL . '/media/' . $filename);
        $mediaAttachment->setPreviewUrl(Config::SITE_URL . '/media/' . $filename);
        $mediaAttachment->setTextUrl(Config::SITE_URL . '/media/' . $filename);
        $mediaAttachment->setRemoteUrl(Config::SITE_URL . '/media/' . $filename);

        // @TODO: Lets do a blurhash that actually isn't very slow
//        $blurhash = Blurhash::encode($this->getImagePixels($imageData), 4, 3);
        $mediaAttachment->setBlurHash('LEHLk~WB2yk8pyo0adR*.7kCMdnj');

        $this->save($mediaAttachment);

        return $mediaAttachment;
    }

    protected function getImagePixels($data): array
    {
        // Yeah, this is definitely slow as f...

        $image = imagecreatefromstring($data);
        $width = imagesx($image);
        $height = imagesy($image);

        $pixels = [];
        for ($y = 0; $y < $height; ++$y) {
            $row = [];
            for ($x = 0; $x < $width; ++$x) {
                $index = imagecolorat($image, $x, $y);
                $colors = imagecolorsforindex($image, $index);

                $row[] = [$colors['red'], $colors['green'], $colors['blue']];
            }

            $pixels[] = $row;
        }
        return $pixels;
    }
}
