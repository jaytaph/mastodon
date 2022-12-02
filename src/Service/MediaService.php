<?php

declare(strict_types=1);

namespace App\Service;

use App\Config;
use App\Entity\MediaAttachment;
use Doctrine\ORM\EntityManagerInterface;
use kornrunner\Blurhash\Blurhash;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Uid\Uuid;

class MediaService
{
    protected EntityManagerInterface $doctrine;
    protected string $imagePath;

    protected const DEFAULT_BLURHASH = '00Pj0^';

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
        $file->move($this->imagePath, $filename);

        $mediaAttachment->setFilename($filename);
        $mediaAttachment->setType('image');
        $mediaAttachment->setDescription('Uploaded via API');
        $mediaAttachment->setUrl(Config::SITE_URL . '/media/images/' . $filename);
        $mediaAttachment->setPreviewUrl(Config::SITE_URL . '/media/images/' . $filename);
        $mediaAttachment->setTextUrl(Config::SITE_URL . '/media/images/' . $filename);
        $mediaAttachment->setRemoteUrl(Config::SITE_URL . '/media/images/' . $filename);

        // @TODO: Lets do a blurhash that actually isn't very slow
        $mediaAttachment->setBlurHash($this->generateBlurhash($this->imagePath . '/' . $filename));
//        $mediaAttachment->setBlurHash('LEHLk~WB2yk8pyo0adR*.7kCMdnj');

        $this->save($mediaAttachment);

        return $mediaAttachment;
    }

    protected function generateBlurhash(string $path): string
    {
        $image = $this->imageCreateFromAny($path);
        if (!$image) {
            return self::DEFAULT_BLURHASH;        // Simple gray block
        }

        $image = imagescale($image, 32, 32, IMG_BICUBIC_FIXED);
        if (!$image) {
            return self::DEFAULT_BLURHASH;
        }
        $width = imagesx($image);
        $height = imagesy($image);

        $pixels = [];
        for ($y = 0; $y < $height; ++$y) {
            $row = [];
            for ($x = 0; $x < $width; ++$x) {
                $index = imagecolorat($image, $x, $y);
                $colors = imagecolorsforindex($image, intval($index));
                if (!$colors) {
                    $row[] = [0, 0, 0];
                }

                $row[] = [$colors['red'], $colors['green'], $colors['blue']];
            }
            $pixels[] = $row;
        }

        $components_x = 4;
        $components_y = 3;
        return Blurhash::encode($pixels, $components_x, $components_y);
    }

    protected function imageCreateFromAny(string $filepath): \GdImage|false
    {
        $info = @getimagesize($filepath);
        if (!is_array($info)) {
            return false;
        }

        $allowedTypes = [ IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG ];
        if (!in_array($info[2], $allowedTypes)) {
            return false;
        }

        switch ($info[2]) {
            case IMAGETYPE_GIF:
                return imageCreateFromGif($filepath);
            case IMAGETYPE_JPEG:
                return imageCreateFromJpeg($filepath);
            case IMAGETYPE_PNG:
                return imageCreateFromPng($filepath);
        }

        return false;
    }

    public function toJson(MediaAttachment $media): array
    {
        return [
            'id' => $media->getId(),
            'type' => 'image', // $media->getType(),
            'url' => $media->getUrl(),
            'preview_url' => $media->getPreviewUrl(),
            'remote_url' => $media->getRemoteUrl(),
//            'meta' => $media->getMeta(),
            'description' => $media->getDescription(),
            'blurhash' => $media->getBlurHash(),
        ];
    }
}
