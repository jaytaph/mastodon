<?php

declare(strict_types=1);

namespace App\Service;

use App\ActivityPub;
use App\Config;
use App\Entity\Account;
use App\Entity\Status;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

class StatusService
{
    protected EntityManagerInterface $doctrine;
    protected AccountService $accountService;
    protected MediaService $mediaService;

    public function __construct(
        EntityManagerInterface $doctrine,
        AccountService $accountService,
        MediaService $mediaService
    ) {
        $this->doctrine = $doctrine;
        $this->accountService = $accountService;
        $this->mediaService = $mediaService;
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

        $status->setUri(Config::SITE_URL . '/users/'.$owner->getAcct().'/status/' . $status->getId()->toBase58());
        $status->setUrl(Config::SITE_URL . '/@'.$owner->getAcct().'/status/' . $status->getId()->toBase58());
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
            'created_at' => $status->getCreatedAt()->format(ActivityPub::DATETIME_FORMAT),
            'in_reply_to_id' => $status->getInReplyTo()?->getId()->toBase58(),
            'in_reply_to_account_id' => $status->getInReplyTo()?->getAccount()->getId()->toBase58(),
            'sensitive' => $status->isSensitive(),
            'spoiler_text' => $status->getContentWarning(),
            'visibility' => $status->getVisibility(),
            'language' => 'en', // $status->getLanguage(),
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
            'application' => [
                'name' => $status->getCreatedWithApplicationId(),
                'vapid_key' => 'BCk-QqERU0q-CfYZjcuB6lnyyOYfJ2AifKqfeGIm7Z-HiTU5T9eTG5GxVA0_OH5mMlI4UkkDTpaZwozy0TzdZ2M='
            ],
            'account' => $this->accountService->toJson($status->getAccount()),
            'media_attachments' => $this->toMediaAttachmentJson($status->getAttachmentIds()),
            'mentions' => $status->getMentionIds(),
            'tags' => $this->toTagJson($status->getTagIds()),
            'emojis' => $status->getEmojiIds(),
            'card' => null,
            'poll' => null,
        ];
    }

    public function getTimelineForAccount(Account $account, bool $local = true, bool $remote = false, bool $onlyMedia = false, string $maxId = '', string $sinceId = '', string $minId = '', int $limit = 40)
    {
        $qb = $this->doctrine->createQueryBuilder()
            ->select('s')
            ->from(Status::class, 's')
            ->where('s.owner = :owner')
            ->setParameter('owner', $account)
            ->orderBy('s.createdAt', 'DESC')
            ->setMaxResults($limit);

        if ($local && !$remote) {
            $qb->andWhere('s.local = true');
        }
        if ($remote && !$local) {
            $qb->andWhere('s.local = false');
        }
        if ($onlyMedia) {
            $qb->andWhere('s.attachmentIds IS NOT NULL');
        }

        if ($minId !== '') {
            $minId = Uuid::fromBase58($minId);
            $status = $this->doctrine->getRepository(Status::class)->find($minId);
            $qb->andWhere('s.createdAt > :minCreatedAt')
                ->setParameter('minCreatedAt', $status->getCreatedAt());
        }
        if ($sinceId !== '') {
            $sinceId = Uuid::fromBase58($sinceId);
            $status = $this->doctrine->getRepository(Status::class)->find($sinceId);
            $qb->andWhere('s.createdAt > :sinceCreatedAt')
                ->setParameter('sinceCreatedAt', $status->getCreatedAt());
        }
        if ($maxId !== '') {
            $maxId = Uuid::fromBase58($maxId);
            $status = $this->doctrine->getRepository(Status::class)->find($maxId);
            $qb->andWhere('s.createdAt < :maxCreatedAt')
                ->setParameter('maxCreatedAt', $status->getCreatedAt());
        }

        $ret = [];
        $result = $qb->getQuery()->getResult();
        foreach ($result as $entry) {
            /** @var Status $entry */
            $ret[] = $this->toJson($entry);
        }

        return $ret;
    }

    protected function toMediaAttachmentJson(array $attachmentIds): array
    {
        $ret = [];

        foreach ($attachmentIds as $id) {
            $media = $this->mediaService->findMediaAttachmentById(Uuid::fromString($id));
            $ret[] = $this->mediaService->toJson($media);
        }

        return $ret;
    }

    protected function toTagJson(array $tagIds): array
    {
        $ret = [];

        foreach ($tagIds as $tag) {
            $ret[] = [
                'name' => $tag['name'],
                'url' => $tag['url'] ?? $tag['href'] ?? '',
                'history' => [],
            ];
        }

        return $ret;
    }
}
