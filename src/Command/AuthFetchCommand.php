<?php

declare(strict_types=1);

namespace App\Command;

use App\Config;
use App\Service\AccountService;
use App\Service\AuthClientService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:fetch:authenticated',
    description: 'Fetches a URL using authentication signatures',
)]
class AuthFetchCommand extends Command
{
    protected AccountService $accountService;
    protected AuthClientService $authClientService;

    /**
     * @param AccountService $accountService
     * @param AuthClientService $authClientService
     */
    public function __construct(AccountService $accountService, AuthClientService $authClientService)
    {
        parent::__construct();

        $this->accountService = $accountService;
        $this->authClientService = $authClientService;
    }


    protected function configure(): void
    {
        $this
            ->addArgument('url', InputArgument::REQUIRED, 'URL to fetch')
        ;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $account = $this->accountService->getAccount(Config::ADMIN_USER);
        $url = strval($input->getArgument('url'));

        $response = $this->authClientService->fetch($account, $url);
        print $response?->getBody()->getContents();

        return Command::SUCCESS;
    }
}
