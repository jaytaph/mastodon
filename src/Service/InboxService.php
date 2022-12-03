<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Account;
use App\Entity\Status;
use Doctrine\ORM\EntityManagerInterface;

class InboxService
{
    protected AccountService $accountService;
    protected StatusService $statusService;
    protected EntityManagerInterface $doctrine;

    public function __construct(AccountService $accountService, StatusService $statusService, EntityManagerInterface $doctrine)
    {
        $this->accountService = $accountService;
        $this->statusService = $statusService;
        $this->doctrine = $doctrine;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getInbox(string $account): void
    {
        // ...
    }

    /**
     * @param array<mixed> $message
     * @throws \Exception
     */
    public function processMessage(Account $source, array $message): bool
    {
        if (!isset($message['type'])) {
            throw new \Exception('type not set');
        }

        switch ($message['type']) {
            case 'Create':
                return $this->processCreatedMessage($source, $message);
//            case 'Update':
//                return $this->processUpdatedMessage($source, $message);
//            case 'Delete':
//                return $this->processDeletedMessage($source, $message);
            default:
//                throw new \Exception('Unknown type: ' . $message['type']);
        }

        return false;
    }

    /**
     * @param array<string> $message
     * @throws \Exception
     */
    protected function processCreatedMessage(Account $source, array $message): bool
    {
        if (! isset($message['object'])) {
            return false;
        }

        /** @var array<string> $object */
        $object = $message['object'];

        switch ($object['type']) {
            case 'Note':
                return $this->processCreatedNoteMessage($source, $message, $object);
            case 'Question':
//                return $this->processCreatedQuestionMessage($message, $object);
                break;
            default:
                throw new \Exception('Unknown object type: ' . $object['type']);
        }

        return false;
    }

    /**
     * @param array<string> $message
     * @param array<string> $object
     * @throws \Exception
     */
    protected function processCreatedNoteMessage(Account $source, array $message, array $object): bool
    {
        // @TODO: We probably need to check for forwarded messages first

        $status = $this->statusService->findStatusByURI($message['id']);
        if (!$status) {
            $status = new Status();
        }

        $account = $this->accountService->findAccountByURI($message['actor']);
        $status->setAccount($account);
        $status->setAccountUri($message['actor']);
        $status->setActivityStreamsType('');  // @TODO ??

        $status->setOwner($source);

        /** @var array<mixed>|string|null $att */
        $att = $object['attachment'];
        $status->setAttachmentIds(is_array($att) ? $att : [$att]);
        $status->setBoostable(false);
//        $status->setBoostOf();
//        $status->setBoostOfAccount();
//        $status->setBoostOfAccountId();
        $status->setContent($object['content']);
        $status->setContentWarning("");
        $status->setCreatedAt(new \DateTime($object['published'] ?? 'now'));
        $status->setCreatedWithApplicationId('');

        /** @var array<mixed>|string|null $emojis */
        $emojis = $object['emoji'] ?? [];
        $status->setEmojiIds(is_array($emojis) ? $emojis : [$emojis]);
        $status->setFederated(false);
//        $status->setInReplyTo($object['inReplyTo'] ?? null);
        if (isset($object['inReplyTo'])) {
//            $account = $this->accountService->findAccount($object['inReplyTo'], true);
//            if ($account) {
//                $status->setInReplyToAccount($account);
//            }
        }
        $status->setInReplyToUri($object['inReplyTo'] ?? '');
        $status->setLanguage('');
        $status->setLikable(true);
        $status->setLocal(false);
        $status->setMentionIds([]);
        $status->setPinned(false);
        $status->setReplyable(true);
        $status->setSensitive($object['sensitive'] == "true");

        /** @var array<mixed>|string|null $tags */
        $tags = $object['tag'];
        if (is_array($tags) && isset($tags['type'])) {
            $tags = [$tags];
        }
        $status->setTagIds(is_array($tags) ? $tags : [$tags]);

        $status->setText($object['content']);
        $status->setUpdatedAt(new \DateTime($object['published'] ?? 'now'));
        $status->setUri($object['id']);
        $status->setUrl($object['url']);
        $status->setVisibility($object['visibility'] ?? 'public');

        $this->doctrine->persist($status);
        $this->doctrine->flush();

        print "Stored " . ($status->getUri() ?? '') . "\n";

        return true;
    }
}
