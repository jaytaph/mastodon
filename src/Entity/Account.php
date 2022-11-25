<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\AccountRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

/**
 * phpmd: @SuppressWarnings(PHPMD.TooManyFields)
 */
#[ORM\Entity(repositoryClass: AccountRepository::class)]
class Account
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private Uuid $id;

    #[ORM\Column(length: 255)]
    private string $username = "";

    #[ORM\Column(length: 255)]
    private string $acct = "";

    #[ORM\Column(length: 255)]
    private string $displayName = "";

    #[ORM\Column]
    private bool $locked = false;

    #[ORM\Column]
    private bool $bot = false;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $lastStatusAt;

    #[ORM\Column(type: Types::TEXT)]
    private string $note = "";

    #[ORM\Column(length: 255)]
    private string $url = "";

    #[ORM\Column(length: 255)]
    private string $avatar = "";

    #[ORM\Column(length: 255)]
    private string $avatarStatic = "";

    #[ORM\Column(length: 255)]
    private string $header = "";

    #[ORM\Column(length: 255)]
    private string $headerStatic = "";

    /** @var array<string, mixed> array  */
    #[ORM\Column(type: Types::JSON)]
    private array $source;

    /** @var array<string, mixed> array  */
    #[ORM\Column(type: Types::JSON)]
    private array $emojis;

    /** @var array<string, mixed> array  */
    #[ORM\Column(type: Types::JSON)]
    private array $fields;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $publicKeyPem = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $privateKeyPem = null;

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getAcct(): string
    {
        return $this->acct;
    }

    public function setAcct(string $acct): self
    {
        $this->acct = $acct;

        return $this;
    }

    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    public function setDisplayName(string $displayName): self
    {
        $this->displayName = $displayName;

        return $this;
    }

    public function isLocked(): bool
    {
        return $this->locked;
    }

    public function setLocked(bool $locked): self
    {
        $this->locked = $locked;

        return $this;
    }

    public function isBot(): bool
    {
        return $this->bot;
    }

    public function setBot(bool $bot): self
    {
        $this->bot = $bot;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getNote(): string
    {
        return $this->note;
    }

    public function setNote(string $note): self
    {
        $this->note = $note;

        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getAvatar(): string
    {
        return $this->avatar;
    }

    public function setAvatar(string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getAvatarStatic(): string
    {
        return $this->avatarStatic;
    }

    public function setAvatarStatic(string $avatarStatic): self
    {
        $this->avatarStatic = $avatarStatic;

        return $this;
    }

    public function getHeader(): string
    {
        return $this->header;
    }

    public function setHeader(string $header): self
    {
        $this->header = $header;

        return $this;
    }

    public function getHeaderStatic(): string
    {
        return $this->headerStatic;
    }

    public function setHeaderStatic(string $headerStatic): self
    {
        $this->headerStatic = $headerStatic;

        return $this;
    }

    /**
     * @return array|mixed[]
     */
    public function getSource(): array
    {
        return $this->source;
    }

    /**
     * @param array|mixed[] $source
     */
    public function setSource(array $source): self
    {
        $this->source = $source;

        return $this;
    }

    /**
     * @return array|mixed[]
     */
    public function getEmojis(): array
    {
        return $this->emojis;
    }

    /**
     * @param array|mixed[] $emojis
     */
    public function setEmojis(array $emojis): self
    {
        $this->emojis = $emojis;

        return $this;
    }

    /**
     * @return array|mixed[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @param array|mixed[] $fields
     */
    public function setFields(array $fields): self
    {
        $this->fields = $fields;

        return $this;
    }

    public function getLastStatusAt(): \DateTimeImmutable
    {
        return $this->lastStatusAt;
    }

    public function setLastStatusAt(\DateTimeImmutable $lastStatusAt): self
    {
        $this->lastStatusAt = $lastStatusAt;

        return $this;
    }

    public function getPublicKeyPem(): ?string
    {
        return $this->publicKeyPem;
    }

    public function setPublicKeyPem(?string $publicKeyPem): self
    {
        $this->publicKeyPem = $publicKeyPem;

        return $this;
    }

    public function getPrivateKeyPem(): ?string
    {
        return $this->privateKeyPem;
    }

    public function setPrivateKeyPem(?string $privateKeyPem): self
    {
        $this->privateKeyPem = $privateKeyPem;

        return $this;
    }
}
