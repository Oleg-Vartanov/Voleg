<?php

namespace App\SplitExpense\Http\V1\Balance;

use App\Core\Enum\Group;
use App\User\Entity\User;
use Symfony\Component\Serializer\Attribute\Groups;

#[Groups([Group::public->value])]
readonly class SeUserBalance
{
    /**
     * @param SeBalanceAmount[] $amounts One entry per currency the pair shares.
     */
    public function __construct(
        public User $user,
        public array $amounts,
    ) {
    }
}
