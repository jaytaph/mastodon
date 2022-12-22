<?php

declare(strict_types=1);

namespace App\Service;

use App\ActivityPub;
use App\Entity\Account;
use App\Entity\Follower;
use App\Entity\Status;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use League\Bundle\OAuth2ServerBundle\Repository\ClientRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Uid\Uuid;
use Jaytaph\TypeArray\TypeArray;

class AccountService
{
    protected EntityManagerInterface $doctrine;
    protected AuthClientService $authClientService;
    protected TokenStorageInterface $tokenStorage;
    protected ClientRepository $clientRepository;
    protected ?Account $loggedinUser = null;
    protected ConfigService $configService;

    public function __construct(
        EntityManagerInterface $doctrine,
        TokenStorageInterface $tokenStorage,
        AuthClientService $authClientService,
        ClientRepository $clientRepository,
        ConfigService $configService
    ) {
        $this->doctrine = $doctrine;
        $this->tokenStorage = $tokenStorage;
        $this->authClientService = $authClientService;
        $this->clientRepository = $clientRepository;
        $this->configService = $configService;


        $user = $this->tokenStorage->getToken()?->getUser();
        if ($user) {
            /** @var User $user */
            $this->setLoggedInAccount($user->getAccount());
        }
    }

    public function getLoggedInApplication(): string
    {
        $token = $this->tokenStorage->getToken();
        if ($token === null) {
            return '';
        }

        $clientId = $token->hasAttribute('oauth_client_id') ? strval($token->getAttribute('oauth_client_id')) : '';
        $client = $this->clientRepository->getClientEntity($clientId);
        return $client?->getName() ?? '';
    }

    public function setLoggedInAccount(?Account $account): void
    {
        $this->loggedinUser = $account;
    }

    public function getLoggedInAccount(): ?Account
    {
        return $this->loggedinUser;
    }

    public function findAccount(string $acct, bool $fetchRemote = false, ?Account $source = null): ?Account
    {
        $account = $this->doctrine->getRepository(Account::class)->findOneBy(['acct' => $acct]);

        if ($fetchRemote && !$account && $source) {
            $account = $this->fetchRemoteAccount($source, $acct);
        }

        return $account;
    }

    /**
     * @throws EntityNotFoundException
     */
    public function getAccount(string $acct, bool $fetchRemote = true, ?Account $source = null): Account
    {
        $account = $this->findAccount($acct);
        if ($account) {
            return $account;
        }

        if ($fetchRemote && $source) {
            $account = $this->fetchRemoteAccount($source, $acct);
        }

        if (!$account) {
            throw new EntityNotFoundException();
        }

        return $account;
    }

    /**
     * @throws EntityNotFoundException
     */
    public function getAccountById(Uuid $uuid): Account
    {
        $account = $this->findAccountById($uuid);
        if (!$account) {
            throw new EntityNotFoundException();
        }

        return $account;
    }

    public function findAccountById(Uuid $uuid): ?Account
    {
        return $this->doctrine->getRepository(Account::class)->find($uuid);
    }

    public function hasAccount(string $acct): bool
    {
        return $this->doctrine->getRepository(Account::class)->findOneBy(['acct' => $acct]) != null;
    }

    public function toProfileJson(Account $account): array
    {
        $accountUrl = $this->configService->getConfig()->getSiteUrl() . '/users/' . $account->getUsername();

        return [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => $accountUrl,
            'type' => 'Person',
            'preferredUsername' => $account->getDisplayName(),
            'name' => $account->getUsername(),
            'summary' => $account->getNote(),
            'inbox' => $accountUrl . '/inbox',
            'outbox' => $accountUrl . '/outbox',
            'publicKey' => [
                'id' => $accountUrl . '#main-key',
                'owner' => $accountUrl,
                'publicKeyPem' => $account->getPublicKeyPem(),
            ],
            'followers' => $accountUrl . '/followers',
            'following' => $accountUrl . '/following',
        ];
    }

    /**
     * @param Account $account
     * @return mixed[]
     */
    public function toJson(?Account $account): array
    {
        if (!$account) {
            return [];
        }

        return [
            'id' => $account->getId()->toBase58(),
            'username' => $account->getUsername(),
            'acct' => $account->getAcct(),
            'url' => $account->getUri(),
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
            'statuses_count' => $this->statusesCount($account),
            'followers_count' => $this->followersCount($account),
            'following_count' => $this->followingCount($account),
            'fields' => $account->getFields(),
            'bot' => $account->isBot(),
        ];
    }

    public function followersCount(Account $account): int
    {
        return $this->doctrine->getRepository(Follower::class)->count(['follow' => $account]);
    }

    public function followingCount(Account $account): int
    {
        return $this->doctrine->getRepository(Follower::class)->count(['user' => $account]);
    }

    public function statusesCount(Account $account): int
    {
        return $this->doctrine->getRepository(Status::class)->count(['account' => $account]);
    }

    /**
     * @return Account[]
     */
    public function getFollowing(Account $account): array
    {
        $ret = [];

        $followers = $this->doctrine->getRepository(Follower::class)->findBy(['follow' => $account]);
        foreach ($followers as $follower) {
            $ret[] = $this->doctrine->getRepository(Account::class)->findOneBy(['acct' => $follower->getUser()->getAcct()]);
        }

        return array_filter($ret);
    }

    /**
     * @return Account[]
     */
    public function getFollowers(Account $account): array
    {
        $ret = [];

        $followers = $this->doctrine->getRepository(Follower::class)->findBy(['user' => $account]);
        foreach ($followers as $follower) {
            $ret[] = $this->doctrine->getRepository(Account::class)->findOneBy(['acct' => $follower->getFollow()->getAcct()]);
        }

        return array_filter($ret);
    }

    public function fetchRemoteAccount(Account $source, string $href): ?Account
    {
        // @TODO: It's not a always needed that we fetch an account as a "user".. we should be able to fetch it as a "client" as well
        $response = $this->authClientService->fetch($source, $href);
        if (! $response) {
            return null;
        }

        $data = TypeArray::fromJson($response->getBody()->getContents());
        if ($data->isEmpty() || !$data->exists('[id]')) {
            return null;
        }

        $acct = $data->getString('[preferredUsername]', '') . "@" . parse_url($data->getString('[id]', ''), PHP_URL_HOST);
        $account = $this->findAccount($acct);
        if (! $account) {
            $account = new Account();
        }

        $account->setUsername($data->getString('[preferredUsername]', ''));
        $account->setAcct($acct);
        $account->setAvatar($data->getString('[icon][url]', ''));
        $account->setHeader($data->getString('[icon][url]', ''));
        $account->setDisplayName($data->getString('[name]', $data->getString('[preferredUsername]', '')));
        $account->setLocked($data->getBool('[manuallyApprovesFollowers]', false));
        $account->setBot($data->getString('[type]', '') == 'Service');
        $account->setUri($data->getString('[id]', ''));
        $account->setCreatedAt(new \DateTimeImmutable());
        $account->setFields($data->getTypeArray('[attachments]', TypeArray::empty()));
        $account->setSource(TypeArray::empty());
        $account->setEmojis(TypeArray::empty());
        $account->setNote($data->getString('[summary]', ''));
        $account->setPublicKeyPem($data->getString('[publicKey][publicKeyPem]', ''));

        $account->setCreatedAt(new \DateTimeImmutable($data->getString('[published]', 'now'), new \DateTimeZone('GMT')));
        $account->setLastStatusAt(new \DateTimeImmutable("now", new \DateTimeZone('GMT')));

        $this->doctrine->persist($account);
        $this->doctrine->flush();

        return $account;
    }

    public function getLocalAccountCount(): int
    {
        $qb = $this->doctrine->getRepository(Account::class)->createQueryBuilder('a');

        /** @var int $total */
        $total = $qb->select('COUNT(a)')
            ->where($qb->expr()->isNotNull('a.privateKeyPem'))
            ->getQuery()
            ->getSingleScalarResult();

        return $total;
    }

    public function findAccountByURI(string $uri, bool $fetchRemote = true, ?Account $source = null): ?Account
    {
        $account = $this->doctrine->getRepository(Account::class)->findOneBy(['uri' => $uri]);
        if (!$account && $fetchRemote && $source) {
            $account = $this->fetchRemoteAccount($source, $uri);
        }

        return $account;
    }

    public function fetchMessageCreator(Account $source, TypeArray $message): ?Account
    {
        $signature = $message->getTypeArrayOrNull('[signature]');
        if ($signature === null) {
            return null;
        }

        // Fetch the creator of the message/signature
        $creator = $signature->getString('[creator]', '');
        $pos = strpos($creator, '#');
        $creator = $pos ? substr($creator, 0, $pos) : $creator;

        return $this->findAccount($creator, true, $source);
    }

    public function follow(Account $source, Account $targetToFollow): void
    {
        $follower = new Follower();
        $follower->setUser($source);
        $follower->setFollow($targetToFollow);
        $follower->setAccepted(false);

        $data = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => $this->configService->getConfig()->getSiteUrl() . '/follow/' . $source->getAcct() . '/' . $targetToFollow->getId()->toBase58(),
            'type' => 'Follow',
            'actor' => $source->getUri(),
            'object' => $targetToFollow->getUri(),
        ];

        $this->authClientService->send($source, $targetToFollow, new TypeArray($data));

        $this->doctrine->persist($follower);
        $this->doctrine->flush();
    }
}
