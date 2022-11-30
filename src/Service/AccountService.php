<?php

declare(strict_types=1);

namespace App\Service;

use App\ActivityPub;
use App\Config;
use App\Entity\Account;
use App\Entity\Follower;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use League\Bundle\OAuth2ServerBundle\Entity\Client;
use League\Bundle\OAuth2ServerBundle\Repository\ClientRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Uid\Uuid;

class AccountService
{
    protected EntityManagerInterface $doctrine;
    protected AuthClientService $authClientService;
    protected TokenStorageInterface $tokenStorage;
    protected ClientRepository $clientRepository;

    public function __construct(
        EntityManagerInterface $doctrine,
        TokenStorageInterface $tokenStorage,
        AuthClientService $authClientService,
        ClientRepository $clientRepository
    ) {
        $this->doctrine = $doctrine;
        $this->tokenStorage = $tokenStorage;
        $this->authClientService = $authClientService;
        $this->clientRepository = $clientRepository;
    }

    public function getLoggedInApplication(): string
    {
        $token = $this->tokenStorage->getToken();
        if ($token === null) {
            return '';
        }

        $clientId = $token->getAttribute('oauth_client_id') ?? 0;

        $client = $this->clientRepository->getClientEntity($clientId);
        return $client?->getName() ?? '';
    }
    /**
     * @throws EntityNotFoundException
     */
    public function getLoggedInAccount(): Account
    {
        $uid = $this->tokenStorage->getToken()?->getUserIdentifier();
        if (!$uid) {
            // When we are not logged in, for instance when running from the commandline, we use the admin user as the default logged in user
            $uid = Config::ADMIN_USER;
        }
        return $this->getAccount((string)$uid, false);
    }

    public function findAccount(string $acct, bool $fetchRemote = false): ?Account
    {
        $account = $this->doctrine->getRepository(Account::class)->findOneBy(['acct' => $acct]);
        if ($fetchRemote && !$account) {
            try {
                $account = $this->fetchRemoteAccount($this->getLoggedInAccount(), $acct);
            } catch (EntityNotFoundException) {
                $account = null;
            }
        }

        return $account;
    }

    /**
     * @throws EntityNotFoundException
     */
    public function getAccount(string $acct, bool $fetchRemote = true): Account
    {
        $account = $this->findAccount($acct);
        if ($account) {
            return $account;
        }

        if ($fetchRemote) {
            $account = $this->fetchRemoteAccount($this->getLoggedInAccount(), $acct);
        }

        if (!$account) {
            throw new EntityNotFoundException();
        }

        return $account;
    }

    /**
     * @throws EntityNotFoundException
     */
    public function getAccountById(string|Uuid $uuid): Account
    {
        $account = $this->findAccountById($uuid);
        if (!$account) {
            throw new EntityNotFoundException();
        }

        return $account;
    }

    public function findAccountById(string|Uuid $uuid): ?Account
    {
        $uuid = ($uuid instanceof Uuid) ? $uuid : Uuid::fromString($uuid);

        return $this->doctrine->getRepository(Account::class)->find($uuid);
    }

    public function hasAccount(string $acct): bool
    {
        return $this->doctrine->getRepository(Account::class)->findOneBy(['acct' => $acct]) != null;
    }

    /**
     * @param Account $account
     * @return mixed[]
     */
    public function toJson(Account $account): array
    {
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
            'statuses_count' => 123,
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

    /**
     * @throws \Exception
     */
    public function fetchRemoteAccount(Account $source, string $href): ?Account
    {
        // @TODO: It's not a always needed that we fetch an account as a "user".. we should be able to fetch it as a "client" as well
        $response = $this->authClientService->fetch($source, $href);
        if (! $response) {
            return null;
        }

        /** @var array<mixed> $data */
        $data = json_decode($response->getBody()->getContents(), true);
        if (!$data || !isset($data['id'])) {
            return null;
        }

        $acct = $data['preferredUsername'] . "@" . parse_url(strval($data['id']), PHP_URL_HOST);
        $account = $this->findAccount($acct);
        if (! $account) {
            $account = new Account();
        }

        $account->setUsername($data['preferredUsername']);
        $account->setAcct($acct);
        $account->setAvatar($data['icon']['url'] ?? '');
        $account->setHeader($data['image']['url'] ?? '');
        $account->setDisplayName($data['name'] ?? $data['preferredUsername']);
        $account->setLocked($data['manuallyApprovesFollowers']);
        $account->setBot($data['type'] == 'Service');
        $account->setUri(strval($data['id']));
        $account->setCreatedAt(new \DateTimeImmutable());
        $account->setFields($data['attachments'] ?? []);
        $account->setSource([]);
        $account->setEmojis([]);
        $account->setNote($data['summary']);
        $account->setPublicKeyPem($data['publicKey']['publicKeyPem']);

        $account->setCreatedAt(new \DateTimeImmutable($data['published'] ?? "now", new \DateTimeZone('GMT')));
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

    public function findAccountByURI(string $uri, bool $fetchRemote = true): ?Account
    {
        print "Loading: " . $uri . "\n";

        $account = $this->doctrine->getRepository(Account::class)->findOneBy(['uri' => $uri]);
        if (!$account && $fetchRemote) {
            $account = $this->fetchRemoteAccount($this->getLoggedInAccount(), $uri);
        }

        return $account;
    }
}
