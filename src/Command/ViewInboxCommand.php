<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\InboxService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Jaytaph\TypeArray\TypeArray;

#[AsCommand(
    name: 'app:inbox:view',
    description: 'Add a short description for your command',
)]
class ViewInboxCommand extends Command
{
    protected InboxService $inboxService;

    public function __construct(InboxService $inboxService)
    {
        parent::__construct();

        $this->inboxService = $inboxService;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('box', InputArgument::REQUIRED, 'filename of box')
            ->addOption('type', 't', InputOption::VALUE_REQUIRED, 'Type')
            ->addOption('raw', 'r', InputOption::VALUE_NONE, 'raw output')
            ->addOption('count', 'c', InputOption::VALUE_NONE, 'count values')
        ;
    }

    /**
     * @SuppressWarnings(PHPMD.NPathComplexity)
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
        $inbox = file(strval($input->getArgument('box')), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($inbox as $line) {
            if ($line[0] == '"') {
                $line = substr(stripslashes($line), 1, -1);
            }
            $message = TypeArray::fromJson($line);
            $i++;
            if ($message->isEmpty()) {
                print "Error reading line $i\n";
                continue;
            }

            $type = $message->getString('[type]', '');
            if (! $this->matchesFilter($filter, $type)) {
                continue;
            }

            if ($mode == "count") {
                if (!isset($counts[$type])) {
                    $counts[$type] = 0;
                }
                $counts[$type]++;
            }

            if ($mode == "raw") {
                print_r($message);
            }

            if ($mode == "table") {
                $obj = $message->isTypeArray('[object]') ?
                    json_encode($message->getTypeArray('[object]'), JSON_PRETTY_PRINT) :
                    $message->getString('[object]', '')
                ;

                $table->addRow([
                    $message->getString('[id]', ''),
                    $message->getString('[type]', ''),
                    $message->getString('[actor]', ''),
                    $obj,
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
