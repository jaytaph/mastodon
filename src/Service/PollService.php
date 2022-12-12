<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Poll;
use App\Entity\Status;
use Doctrine\ORM\EntityManagerInterface;
use Jaytaph\TypeArray\TypeArray;

class PollService
{
    protected EntityManagerInterface $doctrine;

    public function __construct(EntityManagerInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @param Status $status
     * @param TypeArray $pollData
     * @return Poll
     */
    public function createPoll(Status $status, TypeArray $pollData): Poll
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

    public function updatePoll(Poll $poll, TypeArray $data): void
    {
        $this->populate($poll, $data);
    }

    protected function populate(Poll $poll, TypeArray $data): void
    {
        $poll->setExpired($data->exists('[closed]'));

        $poll->setExpiresAt(
            new \DateTimeImmutable($data->getString('[endTime]', '9999-12-31 23:59:59'))
        );

        $poll->setEmojis($data->getTypeArray('[emojis]', TypeArray::empty()));
        $poll->setMultiple($data->exists('[anyOf]'));
        $poll->setOwnVotes(TypeArray::empty());
        $poll->setVotes(TypeArray::empty());
        $poll->setVotersCount($data->getInt('[votersCount]', 0));
        $poll->setVotesCount(0);

        $poll->setOptions(new TypeArray([
            'oneOf' => $data->getTypeArray('[oneOf]', TypeArray::empty()),
            'anyOf' => $data->getTypeArray('[anyOf]', TypeArray::empty())
        ]));

        $this->doctrine->persist($poll);
        $this->doctrine->flush();
    }
}
