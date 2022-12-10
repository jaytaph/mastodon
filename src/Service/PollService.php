<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Poll;
use App\Entity\Status;
use App\JsonArray;
use Doctrine\ORM\EntityManagerInterface;

class PollService
{
    protected EntityManagerInterface $doctrine;

    public function __construct(EntityManagerInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @param Status $status
     * @param JsonArray $pollData
     * @return Poll
     */
    public function createPoll(Status $status, JsonArray $pollData): Poll
    {
        $poll = new Poll();
        $poll->setStatus($status);

        $this->populate($poll, $pollData);

        return $poll;
    }

    public function findByStatus(Status $status): ?Poll
    {
        return $this->doctrine->getRepository(Poll::class)->findOneBy(['status' => $status->getId()]);
    }

    public function updatePoll(Poll $poll, JsonArray $data): void
    {
        $this->populate($poll, $data);
    }

    protected function populate(Poll $poll, JsonArray $data): void
    {
        $poll->setExpired($data->exists('[closed]'));

        $poll->setExpiresAt(
            new \DateTimeImmutable($data->getString('[endTime]', '9999-12-31 23:59:59'))
        );

        $poll->setEmojis($data->getJsonArray('[emojis]', JsonArray::empty()));
        $poll->setMultiple($data->exists('[anyOf]'));
        $poll->setOwnVotes(JsonArray::empty());
        $poll->setVotes(JsonArray::empty());
        $poll->setVotersCount($data->getInt('[votersCount]', 0));
        $poll->setVotesCount(0);

        $poll->setOptions(new JsonArray([
            'oneOf' => $data->getJsonArray('[oneOf]', JsonArray::empty()),
            'anyOf' => $data->getJsonArray('[anyOf]', JsonArray::empty())
        ]));

        $this->doctrine->persist($poll);
        $this->doctrine->flush();
    }
}
