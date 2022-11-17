<?php

namespace App\Command;

use App\Entity\Account;
use App\Service\AccountService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:test',
    description: 'Add a short description for your command',
)]
class TestCommand extends Command
{
    protected AccountService $accountService;

    public function __construct(AccountService $accountService)
    {
        parent::__construct();
        $this->accountService = $accountService;
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $account = new Account();
        $account->setAcct("jaytaph@dhpt.nl");
        $account->setAvatar("https://dhpt.nl/dh.jpg");
        $account->setAvatarStatic("https://dhpt.nl/dh.jpg");
        $account->setBot(false);
        $account->setCreatedAt(new \DateTimeImmutable("now", new \DateTimeZone('GMT')));
        $account->setDisplayName("Jay Taph");
        $account->setEmojis([]);
        $account->setFields([]);
        $account->setHeader("https://dhpt.nl/dh.jpg");
        $account->setHeaderStatic("https://dhpt.nl/dh.jpg");
        $account->setLocked(false);
        $account->setId('jaytaph');
        $account->setNote("I'm JayTaph. I break things.");
        $account->setUsername("jaytaph");
        $account->setSource([]);

        $account->setLastStatusAt(new \DateTimeImmutable("now", new \DateTimeZone('GMT')));

        $this->accountService->storeAccount($account);

        return 0;
    }
}
