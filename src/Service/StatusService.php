<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Account;
use App\Entity\Status;
use Doctrine\ORM\EntityManagerInterface;
use Jaytaph\TypeArray\TypeArray;
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
    protected ConfigService $configService;

    public function __construct(
        EntityManagerInterface $doctrine,
        AccountService $accountService,
        MediaService $mediaService,
        TagService $tagService,
        EmojiService $emojiService,
        PollService $pollService,
        ConfigService $configService
    ) {
        $this->doctrine = $doctrine;
        $this->accountService = $accountService;
        $this->mediaService = $mediaService;
        $this->tagService = $tagService;
        $this->emojiService = $emojiService;
        $this->pollService = $pollService;
        $this->configService = $configService;
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
     * @param TypeArray $data
     * @throws \Exception
     */
    public function createStatus(TypeArray $data, Account $owner, string $applicationId = ''): Status
    {
        // Create new status and persist, so we have a UUID
        $status = new Status();
        $this->doctrine->persist($status);

        // Needs at least a status, mediaIds or Poll
        if ($data->isNullOrNotExists('[status]') && $data->isNullOrNotExists('[mediaIds]') && $data->isNullOrNotExists('[poll]')) {
            throw new \Exception('Status, mediaIds or poll is required');
        }

        // @TODO: check for character limits

        $status->setUri($this->configService->getConfig()->getSiteUrl() . '/users/' . $owner->getAcct() . '/status/' . $status->getId()->toBase58());
        $status->setUrl($this->configService->getConfig()->getSiteUrl() . '/@' . $owner->getAcct() . '/status/' . $status->getId()->toBase58());
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
            $content = $data->getString('[status]');
            $status->setContent('<p>' . $content . '</p>');
            $status->setText($content);
            $status->setMentionIds($this->parseMentions($content));
        }

//        $status->setAttachmentIds($data['media_ids'] ?? []);
        $status->setTagIds([]);
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
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @return Status[]
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
            $ret[] = $entry;
        }

        return $ret;
    }

    public function findStatusById(Uuid $uuid): ?Status
    {
        return $this->doctrine->getRepository(Status::class)->find($uuid);
    }

    /**
     * @return Status[]
     */
    public function getParents(Status $status): array
    {
        return $this->doctrine->getRepository(Status::class)->findBy(['inReplyTo' => $status->getId()]);
    }

    /**
     * Creates a status from an incoming ActivityPub object in the inbox of a user.
     */
    public function createStatusFromActivityPub(Account $owner, TypeArray $object): ?Status
    {
        $status = new Status();

        $author = $this->accountService->findAccountByUri($object->getString('[attributedTo]', ''));      // The creator/author of the status
        if ($author === null) {
            // We cannot find the author who has created this status. That account might have been deleted.
            return null;
        }

        $createdAt = new \DateTime($object->getString('[published]', 'now'));

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
        $status->setCreatedAt($createdAt);
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
        $this->processTags($status, $object->getTypeArray('[tag]', TypeArray::empty()), $createdAt, $author->getAcct());
        $this->processAttachments($status, $object->getTypeArray('[attachment]', TypeArray::empty()));
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

    protected function processAttachments(Status $status, TypeArray $attachments): void
    {
        if ($attachments->exists('[type]')) {
            $attachments = new TypeArray([$attachments]);
        }

        // Create media attachments from the given attachments
        foreach ($attachments->toArray() as $attachment) {
            $attachment = new TypeArray((array)$attachment);
            $media = $this->mediaService->findOrCreateAttachment($attachment);

            $status->addAttachment($media);
        }
    }

    protected function processTags(Status $status, TypeArray $tags, \DateTime $dt, string $acct): void
    {
        if ($tags->exists('[type]')) {
            $tags = new TypeArray([$tags->toArray()]);
        }

        // Create (or update counts for) tags within the message
        foreach ($tags->toArray() as $entry) {
            $entry = new TypeArray((array)$entry);

            if ($entry->getString('[type]', '') == 'Hashtag') {
                $tag = $this->tagService->findOrCreateTag($entry, $dt, $acct);
                $status->addTag($tag);
            }
            if ($entry->getString('[type]', '') == 'Mention') {
                $status->addMention($entry->getString('[href]', ''));
            }
            if ($entry->getString('[type]', '') == 'Emoji') {
                $emoji = $this->emojiService->findOrCreateEmoji($entry);
                $status->addEmoji($emoji);
            }
        }
    }

    /**
     * Parses content and returns an array of mentions URI's
     * @param string $content
     * @return string[]
     */
    protected function parseMentions(string $content): array
    {
        if (!$content) {
            return [];
        }

        // Parse all mentions first
        $mentions = [];
        preg_match_all('/@(([A-Z0-9._%+-]+)(@([A-Z0-9.-]+\.[A-Z]{2,})\b)?)/mi', $content, $matches);
        foreach ($matches[1] as $match) {
            $mentions[] = $match;
        }
        $mentions = array_unique($mentions);

        // resolve mentions to accounts
        $mentionUris = [];
        foreach ($mentions as $mention) {
            $account = $this->accountService->findAccount($mention);
            if ($account) {
                $mentionUris[] = $account->getUri();
            }
        }

        return $mentionUris;
    }
}
