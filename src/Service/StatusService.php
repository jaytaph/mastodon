<?php

declare(strict_types=1);

namespace App\Service;

use App\ActivityPub;
use App\Config;
use App\Entity\Account;
use App\Entity\Status;
use App\Entity\Tag;
use App\JsonArray;
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
     * @param JsonArray $data
     * @throws \Exception
     */
    public function createStatus(JsonArray $data, Account $owner, string $applicationId = ''): Status
    {
        // Create new status and persist, so we have a UUID
        $status = new Status();
        $this->doctrine->persist($status);

        // Needs at least a status, mediaIds or Poll
        if ($data->isNullOrNotExists('[status]') && $data->isNullOrNotExists('[mediaIds]') && $data->isNullOrNotExists('[poll]')) {
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
        $status->setSensitive($data->getBool('[sensitive]', false));
        $status->setVisibility($data->getString('[visibility]', Status::VISIBILITY_PUBLIC));
        $status->setCreatedAt(new \DateTimeImmutable());
        $status->setUpdatedAt(new \DateTimeImmutable());
        $status->setContentWarning($data->getString('[spoiler_text]', ''));
        $status->setCreatedWithApplicationId($applicationId);

        if (! $data->isNullOrNotExists('[status]')) {
            $status->setContent('<p>' . $data->getString('[status]') . '</p>');
            $status->setText($data->getString('[status]'));
        }

//        $status->setAttachmentIds($data['media_ids'] ?? []);
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
     * @return Tag[]
     */
    protected function toTagJson(array $tagIds): array
    {
        $ret = [];

        foreach ($tagIds as $id) {
            $ret[] = $this->tagService->fetch($id);
        }

        return  array_filter($ret);
    }

    /**
     * @param Account $owner
     * @param JsonArray $object
     * @return Status|null
     * @throws \Exception
     */
    public function createStatusFromObject(Account $owner, JsonArray $object): ?Status
    {
        $status = new Status();

        $author = $this->accountService->findAccountByUri($object->getString('[attributedTo]', ''));      // The creator/author of the status
        if ($author === null) {
            // We cannot find the author who has created this status. That account might have been deleted.
            return null;
        }

        $status->setOwner($owner);      // The receiver of the status
        $status->setAccount($author);
        $status->setAccountUri($author->getUri());
        $status->setActivityStreamsType('');  // @TODO ??
        $status->setBoostable(false);
//        $status->setBoostOf();
//        $status->setBoostOfAccount();
//        $status->setBoostOfAccountId();
        // Add URL? (https://docs.joinmastodon.org/spec/activitypub/#payloads)
        $status->setContent($object->getString('[content]', $object->getString('[name]', '')));
        $status->setContentWarning($object->getString('[summary]', ''));
        $status->setCreatedAt(new \DateTime($object->getString('[published]', 'now')));
        $status->setCreatedWithApplicationId('');
        $status->setFederated(false);
        $status->setLanguage('');
        $status->setLikable(true);
        $status->setLocal(false);
        $status->setPinned(false);
        $status->setReplyable(true);
        $status->setSensitive($object->getBool('[sensitive]', false));
        $status->setText($object->getString('[content]', ''));
        $status->setUpdatedAt(new \DateTime($object->getString('[published]', 'now')));
        $status->setUri($object->getString('[id]', ''));
        $status->setUrl($object->getString('[url]', ''));
        $status->setVisibility($object->getString('[visibility]', Status::VISIBILITY_PUBLIC));
        $this->processTags($status, $object->getJsonArray('[tag]', JsonArray::empty()));
        $this->processAttachments($status, $object->getJsonArray('[attachment]', JsonArray::empty()));
//        $status->setMentionIds([]);
//        $this->createEmojis($status, $object['emoji'] ?? []);


        if ($object->exists('[inReplyTo]')) {
            $inReplyTo = $this->findStatusByUri($object->getString('[inReplyTo]', ''));
            if ($inReplyTo !== null) {
                $status->setInReplyTo($inReplyTo);
                $status->setInReplyToUri($inReplyTo->getUri() ?? '');
            }
        }

        if ($object->getString('[type]', '') == 'Question') {
            $this->pollService->createPoll($status, $object);
        }

        $this->doctrine->persist($status);
        $this->doctrine->flush();

        return $status;
    }

    protected function processAttachments(Status $status, JsonArray $attachments): void
    {
        if ($attachments->exists('[type]')) {
            $attachments = new JsonArray([$attachments]);
        }

        // Create media attachments from the given attachments
        foreach ($attachments->toArray() as $attachment) {
            $attachment = new JsonArray((array)$attachment);
            $media = $this->mediaService->findOrCreateAttachment($attachment);

            $status->addAttachment($media);
        }
    }

    protected function processTags(Status $status, JsonArray $tags): void
    {
        if ($tags->exists('[type]')) {
            $tags = new JsonArray([$tags->toArray()]);
        }

        // Create (or update counts for) tags within the message
        foreach ($tags->toArray() as $entry) {
            $entry = new JsonArray((array)$entry);

            if ($entry->getString('[type]', '') == 'Hashtag') {
                $tag = $this->tagService->findOrCreateTag($entry);
                $status->addTag($tag);
            }
            if ($entry->getString('[type]', '') == 'Mention') {
                $account = $this->accountService->findAccountByUri($entry->getString('[href]', ''));
                if ($account) {
                    $status->addMention($account);
                }
            }
            if ($entry->getString('[type]', '') == 'Emoji') {
                $emoji = $this->emojiService->findOrCreateEmoji($entry);
                $status->addEmoji($emoji);
            }
        }
    }

    protected function toPollOptionJson(JsonArray $options): JsonArray
    {
        $oneOf = $options->getJsonArray('[oneOf]', JsonArray::empty());
        if (! $oneOf->isEmpty()) {
            $options = $oneOf;
        } else {
            $options = $options->getJsonArray('[anyOf]', JsonArray::empty());
        }

        $ret = [];
        foreach ($options->toArray() as $option) {
            $option = new JsonArray((array)$option);
            $ret[] = [
                'title' => $option->getString('[name]'),
                'votes_count' => $option->getInt('[replies][totalItems]', 0),
            ];
        }

        return new JsonArray($ret);
    }

    /**
     * @param Uuid[] $emojiIds
     */
    protected function toEmojiJson(array $emojiIds): JsonArray
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

        return new JsonArray($ret);
    }
}
