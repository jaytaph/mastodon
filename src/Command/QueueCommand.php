<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\Queue\Queue;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:queue:process',
    description: 'Processes a single entry from the queue',
)]
class QueueCommand extends Command
{
    protected Queue $queue;

    public function __construct(Queue $queue)
    {
        parent::__construct();

        $this->queue = $queue;
    }


    protected function configure(): void
    {
//        $this
//            ->addArgument('account', InputArgument::REQUIRED, 'Account to fetch as')
//            ->addArgument('url', InputArgument::REQUIRED, 'URL to fetch')
//        ;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->queue->process();

        return Command::SUCCESS;
    }
}
