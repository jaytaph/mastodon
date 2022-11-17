<?php

namespace App\Command;

use App\Entity\Account;
use App\Service\AccountService;
use App\Service\WebfingerService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:user:load',
    description: 'Add a short description for your command',
)]
class UserLoadCommand extends Command
{
    protected AccountService $accountService;
    protected WebfingerService $webfingerService;


    protected function configure(): void
    {
        $this
            ->addArgument('name', InputArgument::REQUIRED, 'User to load into db')
        ;
    }

    public function __construct(AccountService $accountService, WebfingerService $webfingerService)
    {
        parent::__construct();

        $this->accountService = $accountService;
        $this->webfingerService = $webfingerService;
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $io = new SymfonyStyle($input, $output);
        $name = $input->getArgument('name');

        $account = $this->webfingerService->fetch($name);

        if (!$account) {
            $io->error("Cannot find user $name");
            return 1;
        }

        $this->accountService->storeAccount($account);

        $io->success("saved");

        return 0;
    }
}
