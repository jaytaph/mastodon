<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Poll;
use App\Entity\Status;
use Doctrine\ORM\EntityManagerInterface;

class PollService
{
    protected EntityManagerInterface $doctrine;

    public function __construct(EntityManagerInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function createPoll(Status $status, array $pollData): Poll
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

    public function updatePoll(Poll $poll, array $data): void
    {
        $this->populate($poll, $data);
    }

    protected function populate(Poll $poll, array $data)
    {
        $poll->setExpired(isset($data['closed']));
        if ($data['endTime']) {
            $poll->setExpiresAt(new \DateTimeImmutable($data['endTime']));
        } else {
            $poll->setExpiresAt(new \DateTimeImmutable("9999-12-31 23:59:59"));
        }

        $poll->setEmojis($data['emojis'] ?? []);
        $poll->setMultiple(isset($data['anyOf']));
        $poll->setOwnVotes([]);
        $poll->setVotes([]);
        $poll->setVotersCount($data['votersCount'] ?? 0);
        $poll->setVotesCount(0);

        $poll->setOptions([
            'oneOf' => $data['oneOf'] ?? [],
            'anyOf' => $data['anyOf'] ?? [],
        ]);

        $this->doctrine->persist($poll);
        $this->doctrine->flush();
    }
}
