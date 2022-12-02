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
    name: 'app:convert:tags',
    description: 'Converts tags in status to arrays',
)]
class ConvertTagsCommand extends Command
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
            if (! count($status->getTagIds())) {
                print ".";
                continue;
            }

            $tags = $status->getTagIds();
            if (isset($tags['name'])) {
                $status->setTagIds([$status->getTagIds()]);
                $this->doctrine->persist($status);
                print "X";
            } else {
                print ".";
            }
        }
        $this->doctrine->flush();

        return Command::SUCCESS;
    }
}
