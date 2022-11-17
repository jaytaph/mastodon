<?php

namespace App\Command;

use App\Entity\Follower;
use App\Service\AccountService;
use App\Service\WebfingerService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use phpDocumentor\Reflection\DocBlock;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Uid\Uuid;

#[AsCommand(
    name: 'app:user:follow',
    description: 'Add a short description for your command',
)]
class UserFollowCommand extends Command
{
    protected EntityManagerInterface $doctrine;
    protected WebfingerService $webfingerService;
    protected AccountService $accountService;

    public function __construct(EntityManagerInterface $doctrine, WebfingerService $webfingerService, AccountService $accountService)
    {
        parent::__construct();
        $this->doctrine = $doctrine;
        $this->webfingerService = $webfingerService;
        $this->accountService = $accountService;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('user', InputArgument::REQUIRED, 'User who follows')
            ->addArgument('follower', InputArgument::REQUIRED, 'User url to follow')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $userAcct = $input->getArgument('user');
        $followerAcct = $input->getArgument('follower');

        if (! $this->accountService->hasAccount($userAcct)) {
            $this->webfingerService->fetch($userAcct);
        }
        if (! $this->accountService->hasAccount($followerAcct)) {
            $this->webfingerService->fetch($followerAcct);
        }

        $follower = new Follower();
        $follower->setUserId($userAcct);
        $follower->setFollowId($followerAcct);
        $follower->setAccepted(true);
        $this->doctrine->persist($follower);
        $this->doctrine->flush();

        $io->success('All done');
        return Command::SUCCESS;
    }
}
