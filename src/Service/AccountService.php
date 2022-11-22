<?php

declare(strict_types=1);

namespace App\Service;

use App\ActivityPub;
use App\Entity\Account;
use App\Entity\Follower;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Uid\Uuid;

class AccountService
{
    protected EntityManagerInterface $doctrine;

    public function __construct(EntityManagerInterface $doctrine, TokenStorageInterface $tokenStorage)
    {
        $this->doctrine = $doctrine;
        $this->tokenStorage = $tokenStorage;
    }

    public function getLoggedInAccount(): Account
    {
        return $this->getAccount($this->tokenStorage->getToken()->getUserIdentifier());
    }

    public function findAccount(string $acct): ?Account
    {
        return $this->doctrine->getRepository(Account::class)->findOneBy(['acct' => $acct]);
    }

    public function getAccount(string $acct): Account
    {
        $account = $this->findAccount($acct);
        if (!$account) {
            throw new EntityNotFoundException();
        }

        return $account;
    }

    public function getAccountById(Uuid $id): Account
    {
        $account = $this->findAccountById($id);
        if (!$account) {
            throw new EntityNotFoundException();
        }

        return $account;
    }

    public function findAccountById(Uuid $id): ?Account
    {
        return $this->doctrine->getRepository(Account::class)->find($id);
    }

    public function hasAccount(string $acct): bool
    {
        return $this->doctrine->getRepository(Account::class)->findOneBy(['acct' => $acct]) != null;
    }

    public function storeAccount(Account $account): void
    {
        $this->doctrine->persist($account);
        $this->doctrine->flush();
    }

    public function toJson(Account $account): array
    {
        return [
            'id' => $account->getId()->toBase58(),
            'username' => $account->getUsername(),
            'acct' => $account->getAcct(),
            'url' => $account->getUrl(),
            'display_name' => $account->getDisplayName(),
            'note' => $account->getNote(),
            'avatar' => $account->getAvatar(),
            'avatar_static' => $account->getAvatarStatic(),
            'header' => $account->getHeader(),
            'header_static' => $account->getHeaderStatic(),
            'locked' => $account->isLocked(),
            'emojis' => $account->getEmojis(),
            'discoverable' => true,
            'created_at' => $account->getCreatedAt()->format(ActivityPub::DATETIME_FORMAT),
            'last_status_at' => $account->getLastStatusAt()->format(ActivityPub::DATETIME_FORMAT),
            'statuses_count' => $this->statusCount($account),
            'followers_count' => $this->followersCount($account),
            'following_count' => $this->followingCount($account),
            'fields' => $account->getFields(),
            'bot' => $account->isBot(),
        ];
    }

    public function followersCount(Account $account): int
    {
        return $this->doctrine->getRepository(Follower::class)->count(['follow_id' => $account->getId()]);
    }

    public function followingCount(Account $account): int
    {
        return $this->doctrine->getRepository(Follower::class)->count(['id' => $account->getId()]);
    }

    public function statusCount(Account $account): int
    {
        return 123;
//        return $this->doctrine->getRepository(Statuses::class)->count(['acct_id' => $account->getId()]);
    }

    /**
     * @return Account[]
     */
    public function getFollowing(Account $account): array
    {
        $ret = [];

        $followers = $this->doctrine->getRepository(Follower::class)->findBy(['follow_id' => $account->getAcct()]);
        foreach ($followers as $follower) {
            $ret[] = $this->doctrine->getRepository(Account::class)->findOneBy(['acct' => $follower->getUserId()]);
        }

        return $ret;
    }

    /**
     * @return Account[]
     */
    public function getFollowers(Account $account): array
    {
        $ret = [];

        $followers = $this->doctrine->getRepository(Follower::class)->findBy(['user_id' => $account->getAcct()]);
        foreach ($followers as $follower) {
            $ret[] = $this->doctrine->getRepository(Account::class)->findOneBy(['acct' => $follower->getFollowId()]);
        }

        return $ret;
    }
}
