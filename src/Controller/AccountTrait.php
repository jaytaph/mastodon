<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Account;
use App\Service\AccountService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Uid\Uuid;

trait AccountTrait
{
    protected AccountService $accountService;

    protected function findAccount(string $acct, bool $localOnly = false): Account
    {
        if ($localOnly && str_contains($acct, '@')) {
            throw new NotFoundHttpException();
        }
        $account = $this->accountService->findAccount($acct);
        if (!$account) {
            throw new NotFoundHttpException();
        }

        return $account;
    }

    protected function findAccountById(string|Uuid $uuid): Account
    {
        if (!$uuid instanceof Uuid) {
            $uuid = Uuid::fromString($uuid);
        }

        $account = $this->accountService->findAccountById($uuid);
        if (!$account) {
            throw new NotFoundHttpException();
        }

        return $account;
    }
}
