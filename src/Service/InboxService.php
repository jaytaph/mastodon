<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Status;
use Doctrine\ORM\EntityManagerInterface;

class InboxService
{
    protected AccountService $accountService;
    protected EntityManagerInterface $doctrine;

    public function __construct(AccountService $accountService, EntityManagerInterface $doctrine)
    {
        $this->accountService = $accountService;
        $this->doctrine = $doctrine;
    }

    public function getInbox(string $account): array
    {
        // ...
    }

    public function processMessage(array $message): bool
    {
        if (!isset($message['type'])) {
            throw new \Exception('type not set');
        }

        switch ($message['type']) {
            case 'Create':
                return $this->processCreatedMessage($message);
//            case 'Update':
//                return $this->processUpdatedMessage($message);
//            case 'Delete':
//                return $this->processDeletedMessage($message);
            default:
                throw new \Exception('Unknown type: ' . $message['type']);
        }

        return false;
    }

    protected function processCreatedMessage(array $message): bool
    {
        if (! isset($message['object'])) {
            return false;
        }
        $object = $message['object'];

        switch ($object['type']) {
            case 'Note':
                return $this->processCreatedNoteMessage($message, $object);
            case 'Question':
//                return $this->processCreatedQuestionMessage($message, $object);
                break;
            default:
                throw new \Exception('Unknown object type: ' . $object['type']);
        }

        return false;
    }

    protected function processCreatedNoteMessage(array $message, array $object): bool
    {
        // @TODO: We probably need to check for forwarded messages first


//        $account = $this->accountService->findAccount($message['actor'], true);

        $status = new Status();
        $status->setAccount(null);
        $status->setAccountUri($message['actor']);
        $status->setActivityStreamsType('');  // @TODO ??
        $status->setAttachmentIds($object['attachment']);
        $status->setBoostable(false);
//        $status->setBoostOf();
//        $status->setBoostOfAccount();
//        $status->setBoostOfAccountId();
        $status->setContent($object['content']);
        $status->setContentWarning(false);
        $status->setCreatedAt(new \DateTime($object['published'] ?? 'now'));
        $status->setCreatedWithApplicationId('');
        $status->setEmojiIds($object['emoji'] ?? []);
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
        $status->setSensitive($object['sensitive'] ?? false);
        $status->setTagIds($object['tag']);
        $status->setText($object['content']);
        $status->setUpdatedAt(new \DateTime($object['published'] ?? 'now'));
        $status->setUri($object['id']);
        $status->setUrl($object['url']);
        $status->setVisibility(true);


        $this->doctrine->persist($status);
        $this->doctrine->flush();

        print "Stored " . $status->getUri() . "\n";

        return true;
    }
}
