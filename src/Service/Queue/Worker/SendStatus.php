<?php

declare(strict_types=1);

namespace App\Service\Queue\Worker;

use App\ActivityStream;
use App\Entity\Account;
use App\Entity\MediaAttachment;
use App\Entity\Status;
use App\Entity\Tag;
use App\Service\AccountService;
use App\Service\AuthClientService;
use App\Service\SignatureService;
use App\Service\Queue\Queue;
use App\Service\Queue\QueueEntry;
use App\Service\Queue\WorkerInterface;
use App\Service\StatusService;
use Doctrine\ORM\EntityManagerInterface;
use Jaytaph\TypeArray\TypeArray;
use Symfony\Component\Uid\Uuid;

class SendStatus implements WorkerInterface
{
    protected Queue $queue;
    protected EntityManagerInterface $doctrine;
    protected AuthClientService $authClientService;
    protected AccountService $accountService;
    protected StatusService $statusService;
    protected SignatureService $signatureService;

    public const TYPE = 'send_status';

    public function __construct(
        Queue $queue,
        EntityManagerInterface $doctrine,
        AuthClientService $authClientService,
        AccountService $accountService,
        StatusService $statusService,
        SignatureService $signatureService,
    ) {
        $this->queue = $queue;
        $this->doctrine = $doctrine;
        $this->authClientService = $authClientService;
        $this->accountService = $accountService;
        $this->statusService = $statusService;
        $this->signatureService = $signatureService;
    }

    public function canWork(QueueEntry $entry): bool
    {
        return $entry->getType() === self::TYPE;
    }

    public function work(QueueEntry $entry): void
    {
        $uri = $entry->getData()->getString('[uri]');

        $statusId = $entry->getData()->getString('[status_id]');
        $status = $this->statusService->findStatusById(Uuid::fromString($statusId));
        if (!$status) {
            $entry->setFailedReason('Status not found');
            $entry->setFailed(true);
            return;
        }

        $source = $status->getAccount();
        if (!$source) {
            $entry->setFailedReason('Source account not found');
            $entry->setFailed(true);
            return;
        }

        print("Sending status to $uri");

        if ($uri == "as:public" || $uri == "https://www.w3.org/ns/activitystreams#Public") {
            // @TODO: Should we send to something?
            return;
        }

        // Dereference the uri to get the actual host
        $response = $this->authClientService->fetch($source, $uri);
        if (!$response || $response->getStatusCode() != 200) {
            $entry->setFailedReason('Failed to dereference uri');
            $entry->setRetry(true);
            return;
        }

        $body = json_decode($response->getBody()->getContents(), true);
        if (is_array($body)) {
            $body = new TypeArray($body);
        } else {
            $body = TypeArray::empty();
        }

        if (! $body->exists('[type]')) {
            $entry->setFailedReason('Failed to dereference uri');
            $entry->setRetry(true);
            return;
        }

        if ($body->getString('[type]') == 'Collection') {
            // Handle collection
            foreach ($body->getTypeArray('[items]')->toArray() as $uri) {
                $this->queue->queue(self::TYPE, new TypeArray([
                    'uri' => $uri,
                    'status_id' => $status->getId(),
                ]));
            }
            return;
        }
        if ($body->getString('[type]') == 'OrderedCollection') {
            // Handle ordered collection
            foreach ($body->getTypeArray('[orderedItems]')->toArray() as $uri) {
                $this->queue->queue(self::TYPE, new TypeArray([
                    'uri' => $uri,
                    'status_id' => $status->getId(),
                ]));
            }
            return;
        }

        if ($body->getString('[type]') == 'Person') {
            // Handle person
            if (!$body->exists('[inbox]')) {
                $entry->setFailedReason('No inbox');
                $entry->setRetry(true);
                return;
            }

            // @TODO: Convert status to activitystream

            $note = $this->convertToNote($status);
            $created = $this->wrapInCreate($note, $source);

            $inbox = $body->getString('[inbox]');
            $response = $this->authClientService->sendToUri($source, $inbox, $created);
            if (!$response) {
                $entry->setFailedReason('Failed to send to inbox');
                $entry->setRetry(true);
                return;
            }
            if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
                $entry->setFailedReason('Failed to send to inbox');
                $entry->setRetry(true);
            }

            dump($response->getStatusCode());
            dump($response->getBody()->getContents());
            return;
        }

        $entry->setFinished(false);
    }

    protected function wrapInCreate(TypeArray $note, Account $source): TypeArray
    {
        $ret = [
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => $note->getString('[id]') . '/activity',
            'type' => 'Create',
            'actor' => $source->getUri(),
            'published' => $note->getString('[published]'),
            'object' => $note,
        ];

        $ret['signature'] = $this->signatureService->sign($source, new TypeArray($ret));

        $to = $note->getTypeArray('[to]', TypeArray::empty());
        if ($to->exists('[0]')) {
            $ret['to'] = $note->getTypeArray('[to]')->toArray();
        } else {
            // No TO found, so we assume public
            $ret['to'] = 'https://www.w3.org/ns/activitystreams#Public';
        }

        $cc = $note->getTypeArray('[cc]', TypeArray::empty());
        if ($cc->exists('[0]')) {
            $ret['cc'] = $note->getTypeArray('[cc]')->toArray();
        }

        return new TypeArray($ret);
    }

    protected function convertToNote(Status $status): TypeArray
    {
        $note = [
            'id' => $status->getUri(),
            'type' => 'Note',
            'summary' => '',
            'published' => $status->getCreatedAt()?->format(ActivityStream::DATETIME_FORMAT),
            'attributedTo' => $status->getAccount()?->getUri(),
            'to' => $status->getTo(),
            'cc' => $status->getCc(),
            'sensitive' => $status->isSensitive(),
            'content' => $status->getContent(),
            'attachment' => [],
            'tag' => [],
        ];

        array_map(function (Uuid $id) use (&$note) {
            $attachment = $this->doctrine->getRepository(MediaAttachment::class)->find($id);
            if (!$attachment) {
                return;
            }
            $note['attachment'][] = [
                'type' => 'Document',
//                'mediaType' => $attachment->getMimeType(),
                'url' => $attachment->getUrl(),
            ];
        }, $status->getAttachmentIds());

        array_map(function (Uuid $id) use (&$note) {
            $tag = $this->doctrine->getRepository(Tag::class)->find($id);
            if (!$tag) {
                return;
            }
            $note['tag'][] = [
                'type' => 'Mention',
                'href' => $tag->getHref(),
                'name' => $tag->getName(),
            ];
        }, $status->getTagIds());

        // @TODO: In replyTo
        // @TODO: Replies
        return new TypeArray($note);
    }
}
