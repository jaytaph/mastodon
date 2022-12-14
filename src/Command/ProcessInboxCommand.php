<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\AccountService;
use App\Service\InboxService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Jaytaph\TypeArray\TypeArray;

#[AsCommand(
    name: 'app:inbox:process',
    description: 'Processes an inbox to status table',
)]
class ProcessInboxCommand extends Command
{
    protected InboxService $inboxService;
    protected AccountService $accountService;

    public function __construct(InboxService $inboxService, AccountService $accountService)
    {
        parent::__construct();

        $this->inboxService = $inboxService;
        $this->accountService = $accountService;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('account', InputArgument::REQUIRED, 'Account to fetch as')
            ->addArgument('box', InputArgument::REQUIRED, 'filename of box')
            ->addOption('skip', 's', InputOption::VALUE_REQUIRED, 'how many rules to skip', 0)
        ;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $account = $this->accountService->getAccount(strval($input->getArgument('account')));
        $this->accountService->setLoggedInAccount($account);

        $source = $this->accountService->getLoggedInAccount();
        if (!$source) {
            return Command::FAILURE;
        }

        $i = 0;

        $progressBar = new ProgressBar($output);
        $progressBar->start();

        /** @var iterable<string> $inbox */
        $inbox = file(strval($input->getArgument('box')), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($inbox as $line) {
            $i++;
            if ($i <= $input->getOption('skip')) {
                continue;
            }

            // Skip complex escaped lines
            if ($line[0] == '"') {
                continue;
            }

            $message = TypeArray::fromJson($line);
            if ($message->isEmpty()) {
                print "Error reading line $i\n";
                continue;
            }

            $this->inboxService->processMessage($source, $message, validateMessage: false);

            $progressBar->advance();
            if ($i % 100 == 0) {
                $progressBar->display();
            }
        }

        return Command::SUCCESS;
    }
}
