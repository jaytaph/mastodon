<?php

declare(strict_types=1);

namespace App\Service\Converter;

use App\Entity\Account;
use App\Entity\Config;
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

// Converts elements from internal format to mastodon API format

class ApiConverter
{
    public const DATETIME_FORMAT = 'Y-m-d\TH:i:s\Z';
    public const DATETIME_FORMAT_GMT = \DateTimeInterface::RFC7231;

    protected AccountService $accountService;
    protected StatusService $statusService;
    protected ConfigService $configService;
    protected EmojiService $emojiService;
    protected MediaService $mediaService;
    protected TagService $tagService;

    public function config(Config $config): TypeArray
    {
        $data = [
            'uri' => $config->getInstanceDomain(),
            'title' => $config->getInstanceTitle(),
            'description' => $config->getInstanceDescription(),
            'short_description' => $config->getInstanceShortDescription(),
            'email' => $config->getInstanceEmail(),
            'version' => '4.0.1',       // @TODO: Store version in config somewhere
            'languages' => $config->getLanguages(),
            'registrations' => $config->isRegistrationAllowed(),
            'approval_required' => $config->isApprovalRequired(),
            'invites_enabled' => $config->isInviteEnabled(),
            'urls' => [
//                'streaming_api' => 'wss://' . $config->getInstanceDomain() . '/api/v1/streaming',
            ],
            'stats' => [
                'user_count' => $this->accountService->getLocalAccountCount(),
                'status_count' => $this->statusService->getLocalStatusCount(),
                'domain_count' => 1,        // @TODO: hardcoded
            ],
            'thumbnail' => $config->getThumbnailUrl(),
        ];

        $adminAccount = $this->accountService->findAccount($config->getAdminAccount());
        if ($adminAccount) {
            $data['contact_account'] = $this->account($adminAccount)->toArray();
        }

        return new TypeArray($data);
    }

    public function trends(TypeArray $stats): TypeArray
    {
        $ret = [];

        /** @var string[] $stat */
        foreach ($stats->toArray() as $stat) {
            $tag = substr($stat['name'], 1);

            if (! isset($ret[$tag])) {
                $ret[$tag] = [
                    'name' => $tag,
                    'url' => $this->configService->getConfig()->getSiteUrl() . '/tags/' . $tag,
                    'following' => false,
                    'history' => [],
                ];
            }

            $ret[$tag]['history'][] = [
                'date' => $stat['date'],
                'accounts' => $stat['accounts'],
                'uses' => $stat['uses'],
            ];
        }

        return new TypeArray(array_values($ret));
    }

    public function suggestions(TypeArray $suggestions): TypeArray
    {
        $data = [];

        foreach ($suggestions->toArray() as $k => $v) {
            /** @var Account $v */
            $data[] = [
                'source' => $k,
                'account' => $this->account($v)->toArray(),
            ];
        }

        return new TypeArray($data);
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

    /**
     * @param Status[] $ancestors
     * @param Status[] $descendants
     * @return TypeArray
     */
    public function context(array $ancestors, array $descendants): TypeArray
    {
        $data = [
            'ancestors' => [],
            'descendants' => [],
        ];

        foreach ($ancestors as $ancestor) {
            $data['ancestors'][] = $this->status($ancestor)->toArray();
        }
        foreach ($descendants as $descendant) {
            $data['descendants'][] = $this->status($descendant)->toArray();
        }

        return new TypeArray($data);
    }

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
            'reblog' => null,
            'mentions' => $status->getMentionIds(),
            'tags' => [],
//            'tags' => $this->toTagJson($status->getTagIds()),
        ];

        if ($status->getAccount()) {
            $data['account'] = $this->account($status->getAccount())->toArray();
        }

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
            'id' => $poll->getId()->toBase58(),
            'expires_at' => $poll->getExpiresAt()->format(self::DATETIME_FORMAT),
            'expired' => $poll->isExpired(),
            'multiple' => $poll->isMultiple(),
            'votes_count' => $poll->getVotesCount(),
            'voters_count' => $poll->getVotersCount(),
            'voted' => false,
            'own_votes' => [],
            'options' => $this->toPollOptionJson($poll->getOptions()),
            'emojis' => [],
        ];

        return new TypeArray($data);
    }

    protected function toPollOptionJson(TypeArray $options): TypeArray
    {
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

    public function account(Account $account): TypeArray
    {
        $data = [
            'id' => $account->getId()->toBase58(),
            'username' => $account->getUsername(),
            'acct' => $account->getAcct(),
            'url' => $account->getUri(),
            'display_name' => $account->getDisplayName(),
            'note' => $account->getNote(),
            'avatar' => $account->getAvatar(),
            'avatar_static' => $account->getAvatarStatic(),
            'header' => $account->getHeader(),
            'header_static' => $account->getHeaderStatic(),
            'locked' => $account->isLocked(),
            'emojis' => $account->getEmojis(),
            'discoverable' => true,
            'created_at' => $account->getCreatedAt()->format(self::DATETIME_FORMAT),
            'last_status_at' => $account->getLastStatusAt()->format(self::DATETIME_FORMAT),
            'statuses_count' => $this->accountService->statusesCount($account),
            'followers_count' => $this->accountService->followersCount($account),
            'following_count' => $this->accountService->followingCount($account),
            'fields' => $account->getFields(),
            'bot' => $account->isBot(),
        ];

        return new TypeArray($data);
    }
}
