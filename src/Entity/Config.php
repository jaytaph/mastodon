<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ConfigRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ConfigRepository::class)]
#[ORM\Table(name: 'config')]
class Config
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private Uuid $id;

    #[ORM\Column(length: 255)]
    private string $instanceDomain;

    #[ORM\Column(length: 255)]
    private string $instanceTitle;

    #[ORM\Column(type: 'text')]
    private string $instanceDescription;

    #[ORM\Column(length: 255)]
    private string $instanceShortDescription;

    #[ORM\Column(length: 255)]
    private string $instanceEmail;

    /** @var string[] */
    #[ORM\Column(type: 'json')]
    private array $languages;

    #[ORM\Column(type: 'boolean')]
    private bool $registrationAllowed;

    #[ORM\Column(type: 'boolean')]
    private bool $approvalRequired;

    #[ORM\Column(type: 'boolean')]
    private bool $inviteEnabled;

    #[ORM\Column(length: 255)]
    private string $thumbnailUrl;

    #[ORM\Column(length: 255)]
    private string $adminAccount;

    #[ORM\Column]
    private int $statusLength;

    #[ORM\Column]
    private int $mediaAttachments;

    #[ORM\Column]
    private int $charactersPerUrl;

    #[ORM\Column]
    private int $accountTags;

    #[ORM\Column]
    private int $optionsPerPoll;

    #[ORM\Column]
    private int $characersPerOption;

    #[ORM\Column]
    private int $minimumPollExpiration;

    #[ORM\Column]
    private int $maximumPollExpiration;


    public function getUrl(): string
    {
        return 'https://' . $this->instanceDomain;
    }

    /**
     * @return string
     */
    public function getInstanceDomain(): string
    {
        return $this->instanceDomain;
    }

    /**
     * @param string $instanceDomain
     */
    public function setInstanceDomain(string $instanceDomain): void
    {
        $this->instanceDomain = $instanceDomain;
    }

    /**
     * @return string
     */
    public function getInstanceTitle(): string
    {
        return $this->instanceTitle;
    }

    /**
     * @param string $instanceTitle
     */
    public function setInstanceTitle(string $instanceTitle): void
    {
        $this->instanceTitle = $instanceTitle;
    }

    /**
     * @return string
     */
    public function getInstanceDescription(): string
    {
        return $this->instanceDescription;
    }

    /**
     * @param string $instanceDescription
     */
    public function setInstanceDescription(string $instanceDescription): void
    {
        $this->instanceDescription = $instanceDescription;
    }

    /**
     * @return string
     */
    public function getInstanceShortDescription(): string
    {
        return $this->instanceShortDescription;
    }

    /**
     * @param string $instanceShortDescription
     */
    public function setInstanceShortDescription(string $instanceShortDescription): void
    {
        $this->instanceShortDescription = $instanceShortDescription;
    }

    /**
     * @return string
     */
    public function getInstanceEmail(): string
    {
        return $this->instanceEmail;
    }

    /**
     * @param string $instanceEmail
     */
    public function setInstanceEmail(string $instanceEmail): void
    {
        $this->instanceEmail = $instanceEmail;
    }

    /**
     * @return array
     */
    public function getLanguages(): array
    {
        return $this->languages;
    }

    /**
     * @param array $languages
     */
    public function setLanguages(array $languages): void
    {
        $this->languages = $languages;
    }

    /**
     * @return bool
     */
    public function isRegistrationAllowed(): bool
    {
        return $this->registrationAllowed;
    }

    /**
     * @param bool $registrationAllowed
     */
    public function setRegistrationAllowed(bool $registrationAllowed): void
    {
        $this->registrationAllowed = $registrationAllowed;
    }

    /**
     * @return bool
     */
    public function isApprovalRequired(): bool
    {
        return $this->approvalRequired;
    }

    /**
     * @param bool $approvalRequired
     */
    public function setApprovalRequired(bool $approvalRequired): void
    {
        $this->approvalRequired = $approvalRequired;
    }

    /**
     * @return bool
     */
    public function isInviteEnabled(): bool
    {
        return $this->inviteEnabled;
    }

    /**
     * @param bool $inviteEnabled
     */
    public function setInviteEnabled(bool $inviteEnabled): void
    {
        $this->inviteEnabled = $inviteEnabled;
    }

    /**
     * @return string
     */
    public function getThumbnailUrl(): string
    {
        return $this->thumbnailUrl;
    }

    /**
     * @param string $thumbnailUrl
     */
    public function setThumbnailUrl(string $thumbnailUrl): void
    {
        $this->thumbnailUrl = $thumbnailUrl;
    }

    /**
     * @return string
     */
    public function getAdminAccount(): string
    {
        return $this->adminAccount;
    }

    /**
     * @param string $adminAccount
     */
    public function setAdminAccount(string $adminAccount): void
    {
        $this->adminAccount = $adminAccount;
    }

    public function getStatusLength(): int
    {
        return $this->statusLength;
    }

    public function setStatusLength(int $statusLength): self
    {
        $this->statusLength = $statusLength;

        return $this;
    }

    public function getMediaAttachments(): int
    {
        return $this->mediaAttachments;
    }

    public function setMediaAttachments(int $mediaAttachments): self
    {
        $this->mediaAttachments = $mediaAttachments;

        return $this;
    }

    public function getCharactersPerUrl(): int
    {
        return $this->charactersPerUrl;
    }

    public function setCharactersPerUrl(int $charactersPerUrl): self
    {
        $this->charactersPerUrl = $charactersPerUrl;

        return $this;
    }

    public function getAccountTags(): int
    {
        return $this->accountTags;
    }

    public function setAccountTags(int $accountTags): self
    {
        $this->accountTags = $accountTags;

        return $this;
    }

    public function getOptionsPerPoll(): int
    {
        return $this->optionsPerPoll;
    }

    public function setOptionsPerPoll(int $optionsPerPoll): self
    {
        $this->optionsPerPoll = $optionsPerPoll;

        return $this;
    }

    public function getCharacersPerOption(): int
    {
        return $this->characersPerOption;
    }

    public function setCharacersPerOption(int $characersPerOption): self
    {
        $this->characersPerOption = $characersPerOption;

        return $this;
    }

    public function getMinimumPollExpiration(): int
    {
        return $this->minimumPollExpiration;
    }

    public function setMinimumPollExpiration(int $minimumPollExpiration): self
    {
        $this->minimumPollExpiration = $minimumPollExpiration;

        return $this;
    }

    public function getMaximumPollExpiration(): int
    {
        return $this->maximumPollExpiration;
    }

    public function setMaximumPollExpiration(int $maximumPollExpiration): self
    {
        $this->maximumPollExpiration = $maximumPollExpiration;

        return $this;
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }
}
