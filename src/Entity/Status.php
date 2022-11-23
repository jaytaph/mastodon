<?php

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
 */
#[ORM\Entity(repositoryClass: StatusRepository::class)]
class Status
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: Account::class, inversedBy: 'statuses')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Account $account;

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

    #[ORM\Column(type: Types::JSON)]
    private array $attachmentIds;

    #[ORM\Column(type: Types::JSON)]
    private array $tagIds;

    #[ORM\Column(type: Types::JSON)]
    private array $mentionIds;

    #[ORM\Column(type: Types::JSON)]
    private array $emojiIds;

    #[ORM\Column(type: 'boolean', nullable: false)]
    private bool $local;

    #[ORM\Column(type: 'text', nullable: false)]
    private string $accountUri;

    #[ORM\Column(type: 'text')]
    private string $inReplyToUri;

    #[ORM\ManyToOne]
    private Status $inReplyTo;

    #[ORM\ManyToOne]
    private Account $inReplyToAccount;

    #[ORM\ManyToOne]
    private Status $boostOf;

    #[ORM\ManyToOne]
    private Account $boostOfAccount;

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

    public function getAttachmentIds(): array
    {
        return $this->attachmentIds;
    }

    public function setAttachmentIds(array $attachmentIds): self
    {
        $this->attachmentIds = $attachmentIds;

        return $this;
    }

    public function getTagIds(): array
    {
        return $this->tagIds;
    }

    public function setTagIds(array $tagIds): self
    {
        $this->tagIds = $tagIds;

        return $this;
    }

    public function getMentionIds(): array
    {
        return $this->mentionIds;
    }

    public function setMentionIds(array $mentionIds): self
    {
        $this->mentionIds = $mentionIds;

        return $this;
    }

    public function getEmojiIds(): array
    {
        return $this->emojiIds;
    }

    public function setEmojiIds(array $emojiIds): self
    {
        $this->emojiIds = $emojiIds;

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

    public function getBoostOfAccountId(): ?string
    {
        return $this->boostOfAccountId;
    }

    public function setBoostOfAccountId(string $boostOfAccountId): self
    {
        $this->boostOfAccountId = $boostOfAccountId;

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
}
