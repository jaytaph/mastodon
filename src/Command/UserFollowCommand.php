<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Follower;
use App\Service\AccountService;
use App\Service\WebfingerService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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

    /**
     * @throws EntityNotFoundException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $userAcct = strval($input->getArgument('user'));
        $followAccount = strval($input->getArgument('follower'));

        $userAccount = $this->accountService->getAccount($userAcct);
        $followAccount = $this->accountService->getAccount($followAccount);

        $follower = new Follower();
        $follower->setUser($userAccount);
        $follower->setFollow($followAccount);
        $follower->setAccepted(true);
        $this->doctrine->persist($follower);
        $this->doctrine->flush();

        $io->success('All done');
        return Command::SUCCESS;
    }
}
