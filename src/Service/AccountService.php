<?php

namespace App\Service;

use App\Entity\Account;
use App\Entity\Follower;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\Uid\Uuid;

class AccountService
{
    protected EntityManagerInterface $doctrine;

    public function __construct(EntityManagerInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    function getAccount(string $acct): Account
    {
        $account = $this->doctrine->getRepository(Account::class)->findOneBy(['acct' => $acct]);
        if (!$account) {
            throw new EntityNotFoundException();
        }

        return $account;
    }

    function getAccountById(Uuid $id): Account
    {
        $account = $this->doctrine->getRepository(Account::class)->find($id);
        if (!$account) {
            throw new EntityNotFoundException();
        }

        return $account;
    }

    function hasAccount(string $acct): bool
    {
        return $this->doctrine->getRepository(Account::class)->findOneBy(['acct' => $acct]) != null;
    }

    function storeAccount(Account $account): void
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
            'display_name' => $account->getDisplayName(),
            'locked' => $account->isLocked(),
            'bot' => $account->isBot(),
            'created_at' => $account->getCreatedAt()->format(\DateTime::RFC3339),
            'note' => $account->getNote(),
            'url' => $account->getUrl(),
            'avatar' => $account->getAvatar(),
            'avatar_static' => $account->getAvatarStatic(),
            'header' => $account->getHeader(),
            'header_static' => $account->getHeaderStatic(),
            'followers_count' => $this->followersCount($account),
            'following_count' => $this->followingCount($account),
            'statuses_count' => $this->statusCount($account),
            'last_status_at' => $account->getLastStatusAt()->format(\DateTime::RFC3339),
            'emojis' => $account->getEmojis(),
            'fields' => $account->getFields(),
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
