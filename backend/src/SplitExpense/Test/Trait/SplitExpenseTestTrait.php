<?php

namespace App\SplitExpense\Test\Trait;

use App\SplitExpense\Entity\Connection;
use App\SplitExpense\Enum\ConnectionStatusEnum;
use App\SplitExpense\Repository\ConnectionRepository;
use App\User\Entity\User;

trait SplitExpenseTestTrait
{
    protected function createConnection(
        User $userA,
        User $userB,
        ConnectionStatusEnum $status = ConnectionStatusEnum::ACCEPTED,
        bool $flush = true,
    ): Connection {
        $connection = new Connection($userA, $userB, $status);
        $this->getService(ConnectionRepository::class)->save($connection, $flush);

        return $connection;
    }
}
