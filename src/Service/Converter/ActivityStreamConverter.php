<?php

declare(strict_types=1);

namespace App\Service\Converter;

use App\Entity\Account;
use App\Entity\Emoji;
use App\Entity\MediaAttachment;
use App\Entity\Poll;
use App\Entity\Status;
use App\Entity\Tag;
use App\Service\AccountService;
use App\Service\ConfigService;
use App\Service\EmojiService;
use App\Service\MediaService;
use App\Service\StatusService;
use App\Service\TagService;
use Jaytaph\TypeArray\TypeArray;

// Converts elements from internal format to ActivityStream format

class ActivityStreamConverter
{
    public const DATETIME_FORMAT = 'Y-m-d\TH:i:s\Z';
    public const DATETIME_FORMAT_GMT = \DateTimeInterface::RFC7231;

    protected AccountService $accountService;
    protected StatusService $statusService;
    protected ConfigService $configService;
    protected EmojiService $emojiService;
    protected MediaService $mediaService;
    protected TagService $tagService;

    public function status(Status $status): TypeArray
    {
        $data = [
            'id' => $status->getId()->toBase58(),
            'created_at' => $status->getCreatedAt()?->format(self::DATETIME_FORMAT),
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
            'account' => $status->getAccount() ? $this->account($status->getAccount()) : null,
            'reblog' => null,
            'mentions' => $status->getMentionIds(),
            'tags' => [],
//            'tags' => $this->toTagJson($status->getTagIds()),
        ];

        foreach ($status->getAttachmentIds() as $attachmentId) {
            $attachment = $this->mediaService->findMediaAttachmentById($attachmentId);
            if (!$attachment) {
                continue;
            }
            $data['media_attachments'][] = $this->media($attachment);
        }

        foreach ($status->getTagIds() as $tagId) {
            $tag = $this->tagService->fetch($tagId);
            if (!$tag) {
                continue;
            }
            $data['tags'][] = $this->tag($tag);
        }

        foreach ($status->getEmojiIds() as $emojiId) {
            $emoji = $this->emojiService->findEmojiById($emojiId);
            if (!$emoji) {
                continue;
            }
            $data['emojis'][] = $this->emoji($emoji);
        }

        if ($status->getPoll() !== null) {
            $poll = $status->getPoll();
            $data['poll'] = $this->poll($poll);
        }

        if ($status->getCreatedWithApplicationId()) {
            $data['application'] = [
                'name' => $status->getCreatedWithApplicationId(),
                // @TODO: This is not a correct key
                'vapid_key' => 'BCk-AAAAAA-CfYZjcuB6lnyyOYfJ2AifKqfeGIm7Z-HiTU5T9eTG5GxVA0_OH5mMlI4UkkDTpaZwozy0TzdZ2M=',
            ];
        }

        return new TypeArray($data);
    }

    public function emoji(Emoji $emoji): TypeArray
    {
        $data[] = [
            'shortcode' => substr($emoji->getName() ?? '', 1, -1),
            'url' => $emoji->getIconUrl(),
            'static_url' => $emoji->getIconUrl(),
            'visible_in_picker' => false,
            'category' => 'custom',
        ];

        return new TypeArray($data);
    }

    public function poll(Poll $poll): TypeArray
    {
        $data = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'type' => 'Question',
            'name' => 'This question is hardcoded?',
        ];

        if ($poll->isMultiple()) {
            $data['anyOf'] = $this->toPollOptionJson($poll->getOptions());
        } else {
            $data['oneOf'] = $this->toPollOptionJson($poll->getOptions());
        }

        if ($poll->isExpired()) {
            $data['closed'] = $poll->getExpiresAt()?->format(self::DATETIME_FORMAT);
        }

        return new TypeArray($data);
    }

    protected function toPollOptionJson(TypeArray $options): TypeArray
    {
        // @TODO: Convert option to ActivityStream format

        $oneOf = $options->getTypeArray('[oneOf]', TypeArray::empty());
        if (! $oneOf->isEmpty()) {
            $options = $oneOf;
        } else {
            $options = $options->getTypeArray('[anyOf]', TypeArray::empty());
        }

        $ret = [];
        foreach ($options->toArray() as $option) {
            $option = new TypeArray((array)$option);
            $ret[] = [
                'title' => $option->getString('[name]'),
                'votes_count' => $option->getInt('[replies][totalItems]', 0),
            ];
        }

        return new TypeArray($ret);
    }

    public function tag(Tag $tag): TypeArray
    {
        $ret[] = [
            'name' => $tag->getName(),
            'url' => $tag->getHref(),
            'history' => [],
        ];

        return new TypeArray($ret);
    }

    public function media(MediaAttachment $media): TypeArray
    {
        $data = [
            'id' => $media->getId(),
            'type' => $media->getType(),
            'url' => $media->getUrl(),
            'preview_url' => $media->getPreviewUrl(),
            'text_url' => $media->getTextUrl(),
            'remote_url' => $media->getRemoteUrl(),
            'description' => $media->getDescription(),
            'blurhash' => $media->getBlurhash(),
        ];

        if ($media->getMeta()) {
            $data['meta'] = $media->getMeta();
        }

        return new TypeArray($data);
    }

    public function account(Account $account): TypeArray
    {
        $data = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'type' => 'Person',
            'id' => $account->getUri(),
            'following' => $account->getUri() . '/following',
            'followers' => $account->getUri() . '/followers',
            'inbox' => $account->getUri() . '/inbox',
            'outbox' => $account->getUri() . '/outbox',
            'preferredUsername' => $account->getUsername(),
            'name' => $account->getDisplayName(),
            'summary' => $account->getNote(),
            'icon' => [
                $account->getAvatarStatic(),
            ],
        ];

        return new TypeArray($data);
    }


    public function collection(array $elements, string $summary = null): TypeArray
    {
        $data = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'type' => 'Collection',
            'totalItems' => count($elements),
            'items' => $elements,
        ];

        if ($summary) {
            $data['summary'] = $summary;
        }

        return new TypeArray($data);
    }

    public function orderedCollection(array $elements, string $summary = null): TypeArray
    {
        $data = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'type' => 'OrderedCollection',
            'totalItems' => count($elements),
            'orderedItems' => $elements,
        ];

        if ($summary) {
            $data['summary'] = $summary;
        }

        return new TypeArray($data);
    }
}
