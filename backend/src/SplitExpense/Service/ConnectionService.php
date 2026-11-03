<?php

namespace App\SplitExpense\Service;

use App\SplitExpense\Entity\Connection;
use App\SplitExpense\Enum\ConnectionStatusEnum;
use App\SplitExpense\Repository\ConnectionRepository;
use App\User\Entity\User;
use LogicException;

readonly class ConnectionService
{
    public function __construct(
        private ConnectionRepository $repository,
    ) {
    }

    public function requestConnection(Connection $connection): void
    {
        // todo.
//        $this->mailer->send(
//            template: 'email/emailChange.html.twig',
//            to: $connection->getRequestedTo()->getEmail(),
//            subject: 'Split Expense Connection Request',
//            context: [
//                'requestedBy' => $connection->getRequestedBy()
//            ],
//        );
    }

    public function create(User $userA, User $userB): Connection
    {
        if ($userA->getId() === $userB->getId()) {
            throw new LogicException('Cannot add self as connection.');
        }

        $connection = $this->repository->findOneByUsers($userA, $userB);
        if ($connection !== null) {
            if ($connection->getStatus() === ConnectionStatusEnum::ACCEPTED) {
                throw new LogicException('Connection already exists.');
            }
            throw new LogicException('Connection request already exists.');
        }

        return new Connection($userA, $userB);
    }

    public function respond(
        User $user,
        Connection $connection,
        ConnectionStatusEnum $status,
    ): void {
        if ($connection->getStatus() !== ConnectionStatusEnum::PENDING) {
            throw new LogicException('Connection is not pending.');
        }

        if ($connection->getRequestedTo() !== $user) {
            throw new LogicException('Only the requested user can respond.');
        }

        if (!in_array($status, [ConnectionStatusEnum::ACCEPTED, ConnectionStatusEnum::REJECTED], true)) {
            throw new LogicException('Invalid status.');
        }

        $connection->setStatus($status);
    }

    public function isConnected(User $userA, User $userB): bool
    {
        $connection = $this->repository->findOneByUsers($userA, $userB);

        return $connection?->getStatus() === ConnectionStatusEnum::ACCEPTED;
    }
}
