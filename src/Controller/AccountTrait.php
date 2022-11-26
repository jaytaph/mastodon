<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\AccountService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait AccountTrait
{
    protected AccountService $accountService;

    protected function findAccount(string $acct, bool $localOnly = false): \App\Entity\Account
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
}
