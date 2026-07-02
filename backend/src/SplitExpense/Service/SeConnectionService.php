<?php

namespace App\SplitExpense\Service;

use App\SplitExpense\Entity\SeConnection;
use App\SplitExpense\Enum\SeConnectionStatusEnum;
use App\SplitExpense\Repository\SeConnectionRepository;
use App\User\Entity\User;
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
            throw new LogicException('Cannot add self as connection.');
        }

        $connection = $this->repository->findOneByUsers($userA, $userB);
        if ($connection !== null) {
            if ($connection->getStatus() === SeConnectionStatusEnum::ACCEPTED) {
                throw new LogicException('Connection already exists.');
            }
            throw new LogicException('Connection request already exists.');
        }

        return new SeConnection($userA, $userB);
    }

    public function respond(
        User $user,
        SeConnection $connection,
        SeConnectionStatusEnum $status,
    ): void {
        if ($connection->getStatus() !== SeConnectionStatusEnum::PENDING) {
            throw new LogicException('Connection is not pending.');
        }

        if ($connection->getRequestedTo() !== $user) {
            throw new LogicException('Only the requested user can respond.');
        }

        if (!in_array($status, [SeConnectionStatusEnum::ACCEPTED, SeConnectionStatusEnum::REJECTED], true)) {
            throw new LogicException('Invalid status.');
        }

        $connection->setStatus($status);
    }
}
