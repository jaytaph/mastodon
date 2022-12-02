<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Account;
use App\Service\AccountService;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Uid\Uuid;

trait AccountTrait
{
    protected AccountService $accountService;

    protected function findAccount(string $acct, bool $localOnly = false): ?Account
    {
        if ($localOnly && str_contains($acct, '@')) {
            return null;
        }
        $account = $this->accountService->findAccount($acct);
        if (!$account) {
            return null;
        }

        return $account;
    }

    protected function findAccountById(string|Uuid $uuid): ?Account
    {
        if (!$uuid instanceof Uuid) {
            $uuid = Uuid::fromString($uuid);
        }

        return $this->accountService->findAccountById($uuid);
    }

    protected function getOAuthUser(): Account
    {
        $account = $this->accountService->getLoggedInAccount();
        if (!$account) {
            throw new AccessDeniedHttpException('You must be logged in to access this resource.');
        }

        return $account;
    }

}
