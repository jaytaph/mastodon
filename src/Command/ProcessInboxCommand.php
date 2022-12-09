<?php

declare(strict_types=1);

namespace App\Command;

use App\Config;
use App\Service\AccountService;
use App\Service\InboxService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
            ->addArgument('box', InputArgument::REQUIRED, 'filename of box')
        ;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->accountService->setLoggedInAccount(Config::ADMIN_USER);
        $source = $this->accountService->getLoggedInAccount();
        if (!$source) {
            return Command::FAILURE;
        }

        $i = 0;

        $progressBar = new ProgressBar($output);
        $progressBar->start();

        /** @var iterable<string> $inbox */
        $inbox = file($input->getArgument('box'), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($inbox as $line) {
            // Skip complex escaped lines
            if ($line[0] == '"') {
                continue;
            }

            /** @var array<string,string|array<string>> $message */
            $message = json_decode($line, true);
            $i++;
            if (!$message) {
                print "Error reading line $i\n";
                continue;
            }

            $this->inboxService->processMessage($source, $message, validateMessage: true);

            $progressBar->advance();
            if ($i % 100 == 0) {
                $progressBar->display();
            }
        }

        return Command::SUCCESS;
    }
}
