<?php

declare(strict_types=1);

namespace App\Service;

use App\ActivityPub;
use App\Config;
use App\Entity\Account;
use App\Entity\Status;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class StatusService
{
    protected EntityManagerInterface $doctrine;
    protected AccountService $accountService;
    protected MediaService $mediaService;
    protected TagService $tagService;
    protected EmojiService $emojiService;
    protected PollService $pollService;

    public function __construct(
        EntityManagerInterface $doctrine,
        AccountService $accountService,
        MediaService $mediaService,
        TagService $tagService,
        EmojiService $emojiService,
        PollService $pollService
    ) {
        $this->doctrine = $doctrine;
        $this->accountService = $accountService;
        $this->mediaService = $mediaService;
        $this->tagService = $tagService;
        $this->emojiService = $emojiService;
        $this->pollService = $pollService;
    }

    public function getLocalStatusCount(): int
    {
        return $this->doctrine->getRepository(Status::class)->count(['local' => true]);
    }

    public function findStatusByUri(string $uri): ?Status
    {
        return $this->doctrine->getRepository(Status::class)->findOneBy(['uri' => $uri]);
    }

    /**
     * This is different than createStatusFromObject. It would be nice if we can somehow reuse the code.
     *
     * @param mixed[] $data
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

        $status->setUri(Config::SITE_URL . '/users/' . $owner->getAcct() . '/status/' . $status->getId()->toBase58());
        $status->setUrl(Config::SITE_URL . '/@' . $owner->getAcct() . '/status/' . $status->getId()->toBase58());
        $status->setLocal(true);
        $status->setOwner($owner);
        $status->setAccount($owner);
        $status->setAccountUri($owner->getUri());
        $status->setActivityStreamsType('Note');
        $status->setSensitive($data['sensitive'] ?? false);
        $status->setVisibility($data['visibility'] ?? Status::VISIBILITY_PUBLIC);
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


    /**
     * @param Status $status
     * @return mixed[]
     */
    public function toJson(Status $status): array
    {
        $json = [
            'id' => $status->getId()->toBase58(),
            'created_at' => $status->getCreatedAt()?->format(ActivityPub::DATETIME_FORMAT),
            'in_reply_to_id' => $status->getInReplyTo()?->getId()->toBase58(),
            'in_reply_to_account_id' => $status->getInReplyTo()?->getAccount()?->getId()->toBase58(),
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
            'account' => $this->accountService->toJson($status->getAccount()),
            'reblog' => null,
            'media_attachments' => $this->toMediaAttachmentJson($status->getAttachmentIds()),
            'mentions' => [],
            'tags' => [],
            'emojis' => $this->toEmojiJson($status->getEmojiIds()),
//            'mentions' => $status->getMentionIds(),
//            'tags' => $this->toTagJson($status->getTagIds()),
//            'emojis' => $status->getEmojiIds(),
        ];


        if ($status->getPoll() !== null) {
            $poll = $status->getPoll();

            $json['poll'] = [
                'id' => $poll->getId()->toBase58(),
                'expires_at' => $poll->getExpiresAt()->format(ActivityPub::DATETIME_FORMAT),
                'expired' => $poll->isExpired(),
                'multiple' => $poll->isMultiple(),
                'votes_count' => $poll->getVotesCount(),
                'voters_count' => $poll->getVotersCount(),
                'voted' => false,
                'own_votes' => [],
                'options' => $this->toPollOptionJson($poll->getOptions()),
                'emojis' => [],
            ];
        }

        if ($status->getCreatedWithApplicationId()) {
            $json['application'] = [
                'name' => $status->getCreatedWithApplicationId(),
                // @TODO: This is not a correct key
                'vapid_key' => 'BCk-AAAAAA-CfYZjcuB6lnyyOYfJ2AifKqfeGIm7Z-HiTU5T9eTG5GxVA0_OH5mMlI4UkkDTpaZwozy0TzdZ2M=',
            ];
        }

        return $json;
    }

    /**
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @return mixed[]
     */
    public function getTimelineForAccount(
        Account $account,
        bool $local = true,
        bool $remote = false,
        bool $onlyMedia = false,
        string $maxId = '',
        string $sinceId = '',
        string $minId = '',
        int $limit = 40
    ): array {
        $qb = $this->doctrine->createQueryBuilder()
            ->select('s')
            ->from(Status::class, 's')
            ->where('s.owner = :owner')
            ->setParameter('owner', $account->getId())
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
            if ($status) {
                $qb->andWhere('s.createdAt > :minCreatedAt')
                    ->setParameter('minCreatedAt', $status->getCreatedAt());
            }
        }
        if ($sinceId !== '') {
            $sinceId = Uuid::fromBase58($sinceId);
            $status = $this->doctrine->getRepository(Status::class)->find($sinceId);
            if ($status) {
                $qb->andWhere('s.createdAt > :sinceCreatedAt')
                    ->setParameter('sinceCreatedAt', $status->getCreatedAt());
            }
        }
        if ($maxId !== '') {
            $maxId = Uuid::fromBase58($maxId);
            $status = $this->doctrine->getRepository(Status::class)->find($maxId);
            if ($status) {
                $qb->andWhere('s.createdAt < :maxCreatedAt')
                    ->setParameter('maxCreatedAt', $status->getCreatedAt());
            }
        }

        $ret = [];
        $result = $qb->getQuery()->getResult();
        foreach ($result as $entry) {
            if (! $entry->getAccount()) {
                continue;
            }

            /** @var Status $entry */
            $ret[] = $this->toJson($entry);
        }

        return $ret;
    }

    /**
     * @param mixed[] $attachmentIds
     * @return mixed[]
     */
    protected function toMediaAttachmentJson(array $attachmentIds): array
    {
        $ret = [];

        /** @var string $id */
        foreach ($attachmentIds as $id) {
            $media = $this->mediaService->findMediaAttachmentById(Uuid::fromString($id));
            if ($media) {
                $ret[] = $this->mediaService->toJson($media);
            }
        }

        return $ret;
    }

    public function findStatusById(string|Uuid $uuid): ?Status
    {
        if (is_string($uuid)) {
            $uuid = Uuid::fromString($uuid);
        }

        return $this->doctrine->getRepository(Status::class)->find($uuid);
    }

    /**
     * @param Status $status
     * @return Status[]
     */
    public function getParents(Status $status): array
    {
        return $this->doctrine->getRepository(Status::class)->findBy(['inReplyTo' => $status->getId()]);
    }

    /**
     * @param Uuid[] $tagIds
     * @return mixed[]
     */
    protected function toTagJson(array $tagIds): array
    {
        $ret = [];

        foreach ($tagIds as $id) {
            $tag = $this->tagService->fetch($id);
            if ($tag) {
                $ret[] = $tag->toArray();
            }
        }

        return $ret;
    }

    /**
     * @param Account $owner
     * @param array<string,string|string[]> $object
     * @return Status|null
     * @throws \Exception
     */
    public function createStatusFromObject(Account $owner, array $object): ?Status
    {
        $status = new Status();

        /** @phpstan-ignore-next-line */
        $author = $this->accountService->findAccountByUri($object['attributedTo']);      // The creator/author of the status
        if (!$author) {
            // We cannot find the author who has created this status. That account might have been deleted.
        }

        $status->setOwner($owner);      // The receiver of the status
        $status->setAccount($author);
        $status->setAccountUri($author ? $author->getUri() : '');
        $status->setActivityStreamsType('');  // @TODO ??
        $status->setBoostable(false);
//        $status->setBoostOf();
//        $status->setBoostOfAccount();
//        $status->setBoostOfAccountId();
        /** @phpstan-ignore-next-line */
        $status->setContent($object['content'] ?? $object['name']);     // Add URL? (https://docs.joinmastodon.org/spec/activitypub/#payloads)
        /** @phpstan-ignore-next-line */
        $status->setContentWarning($object['summary'] ?? '');
        /** @phpstan-ignore-next-line */
        $status->setCreatedAt(new \DateTime($object['published'] ?? 'now'));
        $status->setCreatedWithApplicationId('');
        $status->setFederated(false);
        $status->setLanguage('');
        $status->setLikable(true);
        $status->setLocal(false);
        $status->setPinned(false);
        $status->setReplyable(true);
        $status->setSensitive($object['sensitive'] == "true");
        /** @phpstan-ignore-next-line */
        $status->setText($object['content']);
        /** @phpstan-ignore-next-line */
        $status->setUpdatedAt(new \DateTime($object['published'] ?? 'now'));
        /** @phpstan-ignore-next-line */
        $status->setUri($object['id']);
        /** @phpstan-ignore-next-line */
        $status->setUrl($object['url']);
        /** @phpstan-ignore-next-line */
        $status->setVisibility($object['visibility'] ?? Status::VISIBILITY_PUBLIC);
        /** @phpstan-ignore-next-line */
        $this->processTags($status, $object['tag'] ?? []);
        /** @phpstan-ignore-next-line */
        $this->processAttachments($status, $object['attachment'] ?? []);
//        $status->setMentionIds([]);
//        $this->createEmojis($status, $object['emoji'] ?? []);


        if (isset($object['inReplyTo'])) {
            /** @phpstan-ignore-next-line */
            $inReplyTo = $this->findStatusByUri($object['inReplyTo']);
            if ($inReplyTo) {
                $status->setInReplyTo($inReplyTo);
                /** @phpstan-ignore-next-line */
                $status->setInReplyToUri($inReplyTo->getUri());
            }
        }

        if ($object['type'] == 'Question') {
            $this->pollService->createPoll($status, $object);
        }

        $this->doctrine->persist($status);
        $this->doctrine->flush();

        return $status;
    }

    /**
     * @param Status $status
     * @param array<string,string|string[]> $attachments
     * @return void
     */
    protected function processAttachments(Status $status, array $attachments): void
    {
        if (isset($attachments['type'])) {
                $attachments = [ $attachments ];
        }

        // Create media attachments from the given attachments
        foreach ($attachments as $attachment) {
            /** @var array<string,string|string[]> $attachment */
            $media = $this->mediaService->findOrCreateAttachment($attachment);

            $status->addAttachment($media);
        }
    }

    /**
     * @param Status $status
     * @param mixed[] $tags
     * @return void
     */
    protected function processTags(Status $status, array $tags): void
    {
        if (isset($tags['type'])) {
            $tags = [ $tags ];
        }

        // Create (or update counts for) tags within the message
        foreach ($tags as $entry) {
            /** @var array<string,string|string[]> $entry */
            if ($entry['type'] == 'Hashtag') {
                $tag = $this->tagService->findOrCreateTag($entry);
                $status->addTag($tag);
            }
            if ($entry['type'] == 'Mention') {
                /** @phpstan-ignore-next-line */
                $account = $this->accountService->findAccountByUri($entry['href']);
                if ($account) {
                    $status->addMention($account);
                }
            }
            if ($entry['type'] == 'Emoji') {
                $emoji = $this->emojiService->findOrCreateEmoji($entry);
                $status->addEmoji($emoji);
            }
        }
    }

    /**
     * @param array<string,string|string[]> $options
     * @return mixed[]
     */
    protected function toPollOptionJson(array $options): array
    {
        /** @phpstan-ignore-next-line */
        if (isset($options['oneOf']) && count($options['oneOf'])) {
            $options = $options['oneOf'];
        } else {
            $options = $options['anyOf'];
        }

        $ret = [];
        /** @var string[] $options */
        foreach ($options as $option) {
            /** @var array<string|string[]> $option */
            $ret[] = [
                'title' => $option['name'],
                'votes_count' => $option['replies']['totalItems'] ?? 0,
            ];
        }

        return $ret;
    }

    /**
     * @param Uuid[] $emojiIds
     * @return mixed[]
     */
    protected function toEmojiJson(array $emojiIds): array
    {
        $ret = [];

        foreach ($emojiIds as $id) {
            $emoji = $this->emojiService->findEmojiById($id);
            if (!$emoji) {
                continue;
            }

            $ret[] = [
                'shortcode' => substr($emoji->getName() ?? '', 1, -1),
                'url' => $emoji->getIconUrl(),
                'static_url' => $emoji->getIconUrl(),
                'visible_in_picker' => false,
                'category' => 'custom',
            ];
        }

        return $ret;
    }
}
