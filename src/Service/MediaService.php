<?php

declare(strict_types=1);

namespace App\Service;

use App\Config;
use App\Entity\MediaAttachment;
use Doctrine\ORM\EntityManagerInterface;
use kornrunner\Blurhash\Blurhash;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Uid\Uuid;
use Jaytaph\TypeArray\TypeArray;

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

    public function createMediaAttachmentFromFile(UploadedFile $file): MediaAttachment
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

        $this->doctrine->persist($mediaAttachment);
        $this->doctrine->flush();

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

        $componentsX = 4;
        $componentsY = 3;
        return Blurhash::encode($pixels, $componentsX, $componentsY);
    }

    /** @SuppressWarnings(PHPMD.ErrorControlOperator) */
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

    /**
     * @return mixed[]
     */
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

    /**
     * @param TypeArray $data
     * @return MediaAttachment
     */
    public function findOrCreateAttachment(TypeArray $data): MediaAttachment
    {
        $media = new MediaAttachment();
        $media->setBlurhash($data->getString('[blurhash]', ''));
        $media->setDescription($data->getString('[description]', ''));
        $media->setFilename($data->getString('[filename]', ''));
        $media->setFocus($data->getTypeArray('[focus]', TypeArray::empty())->toArray());
        $media->setMeta($data->getTypeArray('[meta]', TypeArray::empty())->toArray());
        $media->setPreviewUrl($data->getString('[preview_url]', $data->getString('[url]', '')));
        $media->setRemoteUrl($data->getString('[remote_url]', $data->getString('[url]', '')));
        $media->setTextUrl($data->getString('[text_url]', $data->getString('[url]', '')));
        $media->setType($data->getString('[type]', ''));
        $media->setUrl($data->getString('[url]', ''));

        $this->doctrine->persist($media);
        $this->doctrine->flush();

        return $media;
    }
}
