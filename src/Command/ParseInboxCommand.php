<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:inbox',
    description: 'Add a short description for your command',
)]
class ParseInboxCommand extends Command
{

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $table = new Table($output);
        $table->setStyle('box-double');
        $table->setHeaders(["id", "type", "actor", "object"]);

        $inbox = file("jaytaph-inbox.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($inbox as $line) {
            $line = substr(stripslashes($line), 1, -1);
            $message = json_decode($line, true);
            if (!$message) {
                continue;
            }

            print_r($message);

            $table->addRow([
                $message['id'],
                $message['type'],
                $message['actor'],
                is_array($message['object']) ? "array" : $message['object'],
            ]);
        }

        $table->render();

        return Command::SUCCESS;
    }
}
