<?php

namespace App\SplitExpense\Service;

use App\SplitExpense\Entity\SeConnection;
use App\SplitExpense\Repository\SeConnectionRepository;
use App\User\Entity\User;
use InvalidArgumentException;
use LogicException;

readonly class SeConnectionService
{
    public function __construct(
        private SeConnectionRepository $repository,
    ) {
    }

    public function requestConnection(SeConnection $connection): void
    {
        // todo: send email/notification, then add accept endpoint
        //  $connection->getRequestedBy()
        //  $connection->getRequestedTo()
    }

    public function create(User $userA, User $userB): SeConnection
    {
        if ($userA->getId() === $userB->getId()) {
            throw new InvalidArgumentException('Cannot add self as connection.');
        }

        if ($this->repository->findOneByUsers($userA, $userB) !== null) {
            throw new LogicException('Connection already exists.');
        }

        return new SeConnection($userA, $userB);
    }
}
