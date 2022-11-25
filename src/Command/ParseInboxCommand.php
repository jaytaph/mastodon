<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\InboxService;
use App\Service\SignatureService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:inbox',
    description: 'Add a short description for your command',
)]
class ParseInboxCommand extends Command
{
    protected SignatureService $signatureService;
    protected InboxService $inboxService;

    public function __construct(SignatureService $signatureService, InboxService $inboxService)
    {
        parent::__construct();

        $this->signatureService = $signatureService;
        $this->inboxService = $inboxService;
    }

    protected function configure(): void
    {
        $this
            ->addOption('type', 't', InputOption::VALUE_REQUIRED, 'Type')
            ->addOption('raw', 'r', InputOption::VALUE_NONE, 'raw output')
            ->addOption('count', 'c', InputOption::VALUE_NONE, 'count values')
        ;
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $mode = "table";
        if ($input->getOption('raw')) {
            $mode = "raw";
        }
        if ($input->getOption("count")) {
            $mode = "count";
        }

        // Count mode
        /** @var array<string,int> $counts */
        $counts = [];

        // Table mode
        $table = new Table($output);

        // Table mode
        if ($mode == "table") {
            $table->setStyle('box-double');
            $table->setHeaders(["id", "type", "actor", "object"]);
        }
        if ($mode == "count") {
            $table->setStyle('box-double');
            $table->setHeaders(["type", "count"]);
        }

        $filter = $input->hasOption('type') ? strval($input->getOption('type')) : null;

        $i = 0;
        /** @var iterable<string> $inbox */
        $inbox = file("jaytaph-inbox.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($inbox as $line) {
            if ($line[0] == '"') {
                $line = substr(stripslashes($line), 1, -1);
            }
            /** @var array<string,string|array<string>> $message */
            $message = json_decode($line, true);
            $i++;
            if (!$message) {
                print "Error reading line $i\n";
                continue;
            }

            if (! $this->matchesFilter($filter, strval($message['type']))) {
                continue;
            }

//            try {
//                $this->signatureService->validateMessage($message);
//                print "VALID SIGNATURE ON LINE $i\n";
//            } catch (SignatureValidationException $e) {
//                print "signature error on line $i: {$e->getMessage()}\n";
//                continue;
//            }

            $this->inboxService->processMessage($message);


            if ($mode == "count") {
                $idx = is_string($message['type']) ? $message['type'] : "";
                if (!isset($counts[$idx])) {
                    $counts[$idx] = 0;
                }
                $counts[$idx]++;
            }

            if ($mode == "raw") {
                print_r($message);
            }

            if ($mode == "table") {
                $table->addRow([
                    $message['id'],
                    $message['type'],
                    $message['actor'],
                    is_array($message['object']) ? json_encode(
                        $message['object'],
                        JSON_PRETTY_PRINT
                    ) : $message['object'],
                ]);
            }
        }

        if ($mode == "table") {
            $table->render();
        }

        if ($mode == "count") {
            natsort($counts);
            $counts = array_reverse($counts);
            foreach ($counts as $k => $v) {
                $table->addRow([$k, $v]);
            }
            $table->render();
        }

        return Command::SUCCESS;
    }

    protected function matchesFilter(?string $filter, string $type): bool
    {
        if (! $filter) {
            return true;
        }

        if ($filter[0] == '!') {
            $filter = substr($filter, 1);
            return ($type != $filter);
        }

        return ($type == $filter);
    }
}
