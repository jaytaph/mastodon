<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\MediaAttachment;
use App\Entity\Status;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:convert:media',
    description: 'Converts media in status to media entities',
)]
class ConvertMediaCommand extends Command
{
    protected EntityManagerInterface $doctrine;

    public function __construct(EntityManagerInterface $doctrine)
    {
        parent::__construct();
        $this->doctrine = $doctrine;
    }


    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $entities = $this->doctrine->getRepository(Status::class)->findAll();
        foreach ($entities as $status) {
            if (! count($status->getAttachmentIds())) {
                print ".";
                continue;
            }

            $mediaIds = $status->getAttachmentIds();
            if (isset($mediaIds['type'])) {
                $mediaIds = [$mediaIds];
            }

            $attachmentIds = [];
            foreach ($mediaIds as $mediaId) {
                if (!isset($mediaId['type'])) {
                    $attachmentIds[] = $mediaId;
                    continue;
                }

                $media = new MediaAttachment();
                $media->setBlurhash($mediaId['blurhash'] ?? '');
                $media->setUrl($mediaId['url']);
                $media->setRemoteUrl($mediaId['remoteUrl'] ?? '');
                $media->setPreviewUrl($mediaId['previewUrl'] ?? '');
                $media->setTextUrl($mediaId['textUrl'] ?? '');
                $media->setType($mediaId['type']);
                $media->setDescription($mediaId['description'] ?? '');
                $media->setFocus([]);
                $media->setMeta([]);
                $media->setFilename('');
                $this->doctrine->persist($media);

                $attachmentIds[] = (string)$media->getId();
            }
            $status->setAttachmentIds($attachmentIds);
            $this->doctrine->persist($status);


            print "X";
        }
        $this->doctrine->flush();

        return Command::SUCCESS;
    }
}
