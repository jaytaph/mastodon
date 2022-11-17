<?php

namespace App\Entity;

use App\Repository\AccountRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AccountRepository::class)]
class Account
{
    #[ORM\Id]
    #[ORM\Column]
    private string $id = "";

    #[ORM\Column(length: 255)]
    private string $username = "";

    #[ORM\Column(length: 255)]
    private string $acct = "";

    #[ORM\Column(length: 255)]
    private string $display_name = "";

    #[ORM\Column]
    private bool $locked = false;

    #[ORM\Column]
    private bool $bot = false;

    #[ORM\Column]
    private \DateTimeImmutable $created_at;

    #[ORM\Column]
    private \DateTimeImmutable $last_status_at;

    #[ORM\Column(type: Types::TEXT)]
    private string $note = "";

    #[ORM\Column(length: 255)]
    private string $url = "";

    #[ORM\Column(length: 255)]
    private string $avatar = "";

    #[ORM\Column(length: 255)]
    private string $avatar_static = "";

    #[ORM\Column(length: 255)]
    private string $header = "";

    #[ORM\Column(length: 255)]
    private string $header_static = "";

    #[ORM\Column(type: Types::JSON)]
    private array $source;

    #[ORM\Column(type: Types::JSON)]
    private array $emojis;

    #[ORM\Column(type: Types::JSON)]
    private array $fields;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
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
        return $this->display_name;
    }

    public function setDisplayName(string $display_name): self
    {
        $this->display_name = $display_name;

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
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): self
    {
        $this->created_at = $created_at;

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
        return $this->avatar_static;
    }

    public function setAvatarStatic(string $avatar_static): self
    {
        $this->avatar_static = $avatar_static;

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
        return $this->header_static;
    }

    public function setHeaderStatic(string $header_static): self
    {
        $this->header_static = $header_static;

        return $this;
    }

    public function getSource(): array
    {
        return $this->source;
    }

    public function setSource(array $source): self
    {
        $this->source = $source;

        return $this;
    }

    public function getEmojis(): array
    {
        return $this->emojis;
    }

    public function setEmojis(array $emojis): self
    {
        $this->emojis = $emojis;

        return $this;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function setFields(array $fields): self
    {
        $this->fields = $fields;

        return $this;
    }

    public function getLastStatusAt(): \DateTimeImmutable
    {
        return $this->last_status_at;
    }

    public function setLastStatusAt(\DateTimeImmutable $last_status_at): self
    {
        $this->last_status_at = $last_status_at;

        return $this;
    }

}
