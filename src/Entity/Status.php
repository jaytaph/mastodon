<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\StatusRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
#[ORM\Entity(repositoryClass: StatusRepository::class)]
class Status
{
    public const VISIBILITY_PRIVATE = 'private';
    public const VISIBILITY_PUBLIC = 'public';
    public const VISIBILITY_UNLISTED = 'unlisted';
    public const VISIBILITY_DIRECT = 'direct';

    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: Account::class, inversedBy: 'statuses')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Account $account;

    #[ORM\Column(type: 'text', nullable: false)]
    private string $accountUri;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private \DateTimeInterface $updatedAt;

    #[ORM\Column(type: 'text', nullable: false)]
    private string $uri;

    #[ORM\Column(type: 'text')]
    private string $url;

    #[ORM\Column(type: 'text')]
    private string $content;

    /** @var Uuid[] array  */
    #[ORM\Column(type: Types::JSON, nullable: false)]
    private array $attachmentIds = [];

    /** @var Uuid[] array  */
    #[ORM\Column(type: Types::JSON, nullable: false)]
    private array $tagIds = [];

    /** @var string[] array  */
    #[ORM\Column(type: Types::JSON, nullable: false)]
    private array $mentionIds = [];

    /** @var Uuid[] array  */
    #[ORM\Column(type: Types::JSON, nullable: false)]
    private array $emojiIds = [];

    #[ORM\Column(type: 'boolean', nullable: false)]
    private bool $local;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $inReplyToUri;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Status $inReplyTo;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Account $inReplyToAccount;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Status $boostOf;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?Account $boostOfAccount;

    #[ORM\Column(type: 'text')]
    private string $contentWarning;

    #[ORM\Column(length: 255)]
    private string $visibility;

    #[ORM\Column(type: 'boolean', nullable: false)]
    private bool $sensitive;

    #[ORM\Column(length: 255)]
    private string $language;

    #[ORM\Column(length: 255)]
    private string $createdWithApplicationId;

    #[ORM\Column(length: 255, nullable: false)]
    private string $activityStreamsType;

    #[ORM\Column(type: 'text')]
    private string $text;

    #[ORM\Column(type: 'boolean', nullable: false)]
    private bool $pinned;

    #[ORM\Column(type: 'boolean', nullable: false)]
    private bool $federated;

    #[ORM\Column(type: 'boolean', nullable: false)]
    private bool $boostable;

    #[ORM\Column(type: 'boolean', nullable: false)]
    private bool $replyable;

    #[ORM\Column(type: 'boolean', nullable: false)]
    private bool $likable;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private Account $owner;

    #[ORM\OneToOne(mappedBy: 'status', cascade: ['persist', 'remove'])]
    private ?Poll $poll = null;

    /** @var string[]|null */
    #[ORM\Column(name: '_to', type: 'json', nullable: true)]
    private ?array $to_ = [];

    /** @var string[]|null */
    #[ORM\Column(nullable: true)]
    private ?array $bto = [];

    /** @var string[]|null */
    #[ORM\Column(nullable: true)]
    private ?array $cc = [];

    /** @var string[]|null */
    #[ORM\Column(nullable: true)]
    private ?array $bcc = [];

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getUri(): ?string
    {
        return $this->uri;
    }

    public function setUri(string $uri): self
    {
        $this->uri = $uri;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return Uuid[]
     */
    public function getAttachmentIds(): array
    {
        $this->attachmentIds = $this->convertUuids($this->attachmentIds);

        return $this->attachmentIds;
    }

    /**
     * @param Uuid[] $attachmentIds
     * @return $this
     */
    public function setAttachmentIds(array $attachmentIds): self
    {
        $this->attachmentIds = $this->convertUuids($attachmentIds);

        return $this;
    }

    /**
     * @return Uuid[]
     */
    public function getTagIds(): array
    {
        $this->tagIds = $this->convertUuids($this->tagIds);

        return $this->tagIds;
    }

    /**
     * @param Uuid[] $tagIds
     * @return $this
     */
    public function setTagIds(array $tagIds): self
    {
        $this->tagIds = $this->convertUuids($tagIds);

        return $this;
    }

    /**
     * @return string[]
     */
    public function getMentionIds(): array
    {
        return $this->mentionIds;
    }

    /**
     * @param string[] $mentionIds
     * @return $this
     */
    public function setMentionIds(array $mentionIds): self
    {
        $this->mentionIds = $mentionIds;

        return $this;
    }

    /**
     * @return Uuid[]
     */
    public function getEmojiIds(): array
    {
        $this->emojiIds = $this->convertUuids($this->emojiIds);

        return $this->emojiIds;
    }

    /**
     * @param Uuid[] $emojiIds
     * @return $this
     */
    public function setEmojiIds(array $emojiIds): self
    {
        $this->emojiIds = $this->convertUuids($emojiIds);

        return $this;
    }

    public function isLocal(): ?bool
    {
        return $this->local;
    }

    public function setLocal(bool $local): self
    {
        $this->local = $local;

        return $this;
    }

    public function getAccountUri(): ?string
    {
        return $this->accountUri;
    }

    public function setAccountUri(string $accountUri): self
    {
        $this->accountUri = $accountUri;

        return $this;
    }


    public function getInReplyToUri(): ?string
    {
        return $this->inReplyToUri;
    }

    public function setInReplyToUri(string $inReplyToUri): self
    {
        $this->inReplyToUri = $inReplyToUri;

        return $this;
    }

    public function getContentWarning(): ?string
    {
        return $this->contentWarning;
    }

    public function setContentWarning(string $contentWarning): self
    {
        $this->contentWarning = $contentWarning;

        return $this;
    }

    public function isSensitive(): ?bool
    {
        return $this->sensitive;
    }

    public function setSensitive(bool $sensitive): self
    {
        $this->sensitive = $sensitive;

        return $this;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(string $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function getCreatedWithApplicationId(): ?string
    {
        return $this->createdWithApplicationId;
    }

    public function setCreatedWithApplicationId(string $createdWithApplicationId): self
    {
        $this->createdWithApplicationId = $createdWithApplicationId;

        return $this;
    }

    public function getActivityStreamsType(): ?string
    {
        return $this->activityStreamsType;
    }

    public function setActivityStreamsType(string $activityStreamsType): self
    {
        $this->activityStreamsType = $activityStreamsType;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function isPinned(): ?bool
    {
        return $this->pinned;
    }

    public function setPinned(bool $pinned): self
    {
        $this->pinned = $pinned;

        return $this;
    }

    public function isFederated(): ?bool
    {
        return $this->federated;
    }

    public function setFederated(bool $federated): self
    {
        $this->federated = $federated;

        return $this;
    }

    public function isBoostable(): ?bool
    {
        return $this->boostable;
    }

    public function setBoostable(bool $boostable): self
    {
        $this->boostable = $boostable;

        return $this;
    }

    public function isReplyable(): ?bool
    {
        return $this->replyable;
    }

    public function setReplyable(bool $replyable): self
    {
        $this->replyable = $replyable;

        return $this;
    }

    public function isLikable(): ?bool
    {
        return $this->likable;
    }

    public function setLikable(bool $likable): self
    {
        $this->likable = $likable;

        return $this;
    }

    public function getAccount(): ?Account
    {
        return $this->account;
    }

    public function setAccount(?Account $account): self
    {
        $this->account = $account;

        return $this;
    }

    public function getInReplyTo(): ?self
    {
        return $this->inReplyTo;
    }

    public function setInReplyTo(?self $inReplyTo): self
    {
        $this->inReplyTo = $inReplyTo;

        return $this;
    }

    public function getInReplyToAccount(): ?Account
    {
        return $this->inReplyToAccount;
    }

    public function setInReplyToAccount(?Account $inReplyToAccount): self
    {
        $this->inReplyToAccount = $inReplyToAccount;

        return $this;
    }

    public function getBoostOf(): ?self
    {
        return $this->boostOf;
    }

    public function setBoostOf(?self $boostOf): self
    {
        $this->boostOf = $boostOf;

        return $this;
    }

    public function getBoostOfAccount(): ?Account
    {
        return $this->boostOfAccount;
    }

    public function setBoostOfAccount(?Account $boostOfAccount): self
    {
        $this->boostOfAccount = $boostOfAccount;

        return $this;
    }

    /**
     * @return string
     */
    public function getVisibility(): string
    {
        return $this->visibility;
    }

    /**
     * @param string $visibility
     */
    public function setVisibility(string $visibility): void
    {
        $this->visibility = $visibility;
    }

    public function getOwner(): Account
    {
        return $this->owner;
    }

    public function setOwner(Account $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function addMention(string $uri): void
    {
        $this->mentionIds[] = $uri;
    }

    public function addTag(Tag $tag): void
    {
        $this->tagIds[] = $tag->getId();
    }

    public function addEmoji(Emoji $emoji): void
    {
        $this->emojiIds[] = $emoji->getId();
    }

    public function addAttachment(MediaAttachment $media): void
    {
        $this->attachmentIds[] = $media->getId();
    }

    public function isPrivate(): bool
    {
        return $this->visibility === self::VISIBILITY_PRIVATE;
    }

    public function getPoll(): ?Poll
    {
        return $this->poll;
    }

    public function setPoll(?Poll $poll): self
    {
        // unset the owning side of the relation if necessary
        if ($poll === null && $this->poll !== null) {
            $this->poll->setStatus(null);
        }

        // set the owning side of the relation if necessary
        if ($poll !== null && $poll->getStatus() !== $this) {
            $poll->setStatus($this);
        }

        $this->poll = $poll;

        return $this;
    }

    /**
     * @param mixed[] $ids
     * @return Uuid[]
     */
    protected function convertUuids(array $ids): array
    {
        foreach ($ids as $k => $id) {
            if ($id instanceof Uuid) {
                continue;
            }
            $ids[$k] = Uuid::fromString($id);
        }

        return $ids;
    }

    /**
     * @return string[]
     */
    public function getTo(): array
    {
        return $this->to_ ?? [];
    }

    /**
     * @param string[] $to
     * @return $this
     */
    public function setTo(array $to): self
    {
        $this->to_ = $to;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getBto(): array
    {
        return $this->bto ?? [];
    }

    /**
     * @param string[] $bto
     * @return $this
     */
    public function setBto(array $bto): self
    {
        $this->bto = $bto;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getCc(): array
    {
        return $this->cc ?? [];
    }

    /**
     * @param string[] $cc
     * @return $this
     */
    public function setCc(array $cc): self
    {
        $this->cc = $cc;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getBcc(): array
    {
        return $this->bcc ?? [];
    }

    /**
     * @param string[] $bcc
     * @return $this
     */
    public function setBcc(array $bcc): self
    {
        $this->bcc = $bcc;

        return $this;
    }
}
