<?php

namespace App\SplitExpense\Test\Trait;

use App\SplitExpense\Entity\SeConnection;
use App\SplitExpense\Enum\SeConnectionStatusEnum;
use App\SplitExpense\Repository\SeConnectionRepository;
use App\User\Entity\User;

trait SplitExpenseTestTrait
{
    protected function createConnection(
        User $userA,
        User $userB,
        SeConnectionStatusEnum $status = SeConnectionStatusEnum::ACCEPTED,
        bool $flush = true,
    ): SeConnection {
        $connection = new SeConnection($userA, $userB, $status);
        $this->getService(SeConnectionRepository::class)->save($connection, $flush);

        return $connection;
    }
}
