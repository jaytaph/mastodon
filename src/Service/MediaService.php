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
        $file->move($this->imagePath, $filename);

        $mediaAttachment->setFilename($filename);
        $mediaAttachment->setType('image');
        $mediaAttachment->setDescription('Uploaded via API');
        $mediaAttachment->setUrl(Config::SITE_URL . '/media/images/' . $filename);
        $mediaAttachment->setPreviewUrl(Config::SITE_URL . '/media/images/' . $filename);
        $mediaAttachment->setTextUrl(Config::SITE_URL . '/media/images/' . $filename);
        $mediaAttachment->setRemoteUrl(Config::SITE_URL . '/media/images/' . $filename);

        // @TODO: Lets do a blurhash that actually isn't very slow
        $mediaAttachment->setBlurHash($this->generateBlurhash($this->imagePath .'/'. $filename));
//        $mediaAttachment->setBlurHash('LEHLk~WB2yk8pyo0adR*.7kCMdnj');

        $this->save($mediaAttachment);

        return $mediaAttachment;
    }

    protected function generateBlurhash(string $path): string
    {
        $image = $this->imageCreateFromAny($path);
        if (!$image) {
            return '00Pj0^';        // Simple gray block
        }

        $image = imagescale($image, 32, 32, IMG_BICUBIC_FIXED);
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

        $components_x = 4;
        $components_y = 3;
        return Blurhash::encode($pixels, $components_x, $components_y);
    }

    function imageCreateFromAny(string $filepath): \GdImage|false
    {
        $info = @getimagesize($filepath);
        if (!is_array($info)) {
            return false;
        }

        $allowedTypes = array(
            IMAGETYPE_GIF,  // [] gif
            IMAGETYPE_JPEG,  // [] jpg
            IMAGETYPE_PNG,  // [] png
            //6   // [] bmp
        );
        if (!in_array($info[2], $allowedTypes)) {
            return false;
        }
        switch ($info[2]) {
            case IMAGETYPE_GIF :
                $im = imageCreateFromGif($filepath);
            break;
            case IMAGETYPE_JPEG :
                $im = imageCreateFromJpeg($filepath);
            break;
            case IMAGETYPE_PNG :
                $im = imageCreateFromPng($filepath);
            break;
//            case 6 :
//                $im = imageCreateFromBmp($filepath);
//            break;
        }

        return $im;
    }
}
