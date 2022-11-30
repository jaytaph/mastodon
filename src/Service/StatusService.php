<?php

declare(strict_types=1);

namespace App\Service;

use App\Config;
use App\Entity\Account;
use App\Entity\Status;
use Doctrine\ORM\EntityManagerInterface;

class StatusService
{
    protected EntityManagerInterface $doctrine;
    protected AccountService $accountService;

    public function __construct(EntityManagerInterface $doctrine, AccountService $accountService)
    {
        $this->doctrine = $doctrine;
        $this->accountService = $accountService;
    }

    public function getLocalStatusCount(): int
    {
        return $this->doctrine->getRepository(Status::class)->count(['local' => true]);
    }

    public function findStatusByURI(string $uri): ?Status
    {
        return $this->doctrine->getRepository(Status::class)->findOneBy(['uri' => $uri]);
    }

    /**
     * @throws \Exception
     */
    public function createStatus(array $data, Account $owner, string $applicationId = ''): Status
    {
        // Create new status and persist, so we have a UUID
        $status = new Status();
        $this->doctrine->persist($status);

        // Needs at least a status, mediaIds or Poll
        if ($data['status'] === null && $data['mediaIds'] === null && $data['poll'] === null) {
            throw new \Exception('Status, mediaIds or poll is required');
        }

        // @TODO: check for character limits

        $status->setUri(Config::SITE_URL . '/status/' . $status->getId()->toBase58());
        $status->setUrl(Config::SITE_URL . '/status/' . $status->getId()->toBase58());
        $status->setLocal(true);
        $status->setOwner($owner);
        $status->setAccount($owner);
        $status->setAccountUri($owner->getUri());
        $status->setActivityStreamsType('Note');
        $status->setSensitive($data['sensitive'] ?? false);
        $status->setVisibility($data['visibility'] ?? 'public');
        $status->setCreatedAt(new \DateTimeImmutable());
        $status->setUpdatedAt(new \DateTimeImmutable());
        $status->setContentWarning($data['spoiler_text'] ?? '');
        $status->setCreatedWithApplicationId($applicationId);

        if ($data['status'] !== null) {
            $status->setContent('<p>' . $data['status'] . '</p>');
            $status->setText($data['status']);
        }

        $status->setAttachmentIds($data['media_ids'] ?? []);
        $status->setTagIds([]);
        $status->setMentionIds([]);
        $status->setEmojiIds([]);
        $status->setInReplyTo(null);
        $status->setInReplyToUri('');
        $status->setLanguage('');
        $status->setPinned(false);
        $status->setFederated(false);
        $status->setBoostable(true);
        $status->setReplyable(true);
        $status->setLikable(true);

        $this->doctrine->persist($status);
        $this->doctrine->flush();

        return $status;
    }


    public function toJson(Status $status): array
    {
        return [
            'id' => $status->getId()->toBase58(),
            'created_at' => $status->getCreatedAt()->format(\DateTimeInterface::ATOM),
            'in_reply_to_id' => $status->getInReplyTo()?->getId()->toBase58(),
            'in_reply_to_account_id' => $status->getInReplyTo()?->getAccount()->getId()->toBase58(),
            'sensitive' => $status->isSensitive(),
            'spoiler_text' => $status->getContentWarning(),
            'visibility' => $status->getVisibility(),
            'language' => $status->getLanguage(),
            'uri' => $status->getUri(),
            'url' => $status->getUrl(),
            'replies_count' => 0,
            'reblogs_count' => 0,
            'favourites_count' => 0,
            'favourited' => false,
            'reblogged' => false,
            'muted' => false,
            'bookmarked' => false,
            'pinned' => false,
            'content' => $status->getContent(),
            'reblog' => null,
            'application' => $status->getCreatedWithApplicationId(),
            'account' => $this->accountService->toJson($status->getOwner()),
            'media_attachments' => $status->getAttachmentIds(),
            'mentions' => $status->getMentionIds(),
            'tags' => $status->getTagIds(),
            'emojis' => $status->getEmojiIds(),
            'card' => null,
            'poll' => null,
        ];
    }
}
