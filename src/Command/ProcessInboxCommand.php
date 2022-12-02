<?php

declare(strict_types=1);

namespace App\Command;

use App\Config;
use App\Service\AccountService;
use App\Service\InboxService;
use App\Service\SignatureService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:inbox:process',
    description: 'Processes an inbox to status table',
)]
class ProcessInboxCommand extends Command
{
    protected SignatureService $signatureService;
    protected InboxService $inboxService;
    protected AccountService $accountService;

    public function __construct(SignatureService $signatureService, InboxService $inboxService, AccountService $accountService)
    {
        parent::__construct();

        $this->signatureService = $signatureService;
        $this->inboxService = $inboxService;
        $this->accountService = $accountService;
    }

    /**
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->accountService->setLoggedInAccount(Config::ADMIN_USER);

        $i = 0;

        $progressBar = new ProgressBar($output);
        $progressBar->start();

        /** @var iterable<string> $inbox */
        $inbox = file("jaytaph-inbox.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
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

            $this->inboxService->processMessage($message);

            $progressBar->advance();
            if ($i % 100 == 0) {
                $progressBar->display();
            }
        }

        return Command::SUCCESS;
    }
}
