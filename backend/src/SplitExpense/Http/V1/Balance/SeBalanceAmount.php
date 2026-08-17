<?php

namespace App\SplitExpense\Http\V1\Balance;

use App\Core\Entity\Currency;
use App\Core\Enum\Group;
use Symfony\Component\Serializer\Attribute\Groups;

#[Groups([Group::public->value])]
readonly class SeBalanceAmount
{
    /**
     * @param int $amount Minor units. Positive when the current user is owed,
     *                    negative when the current user owes.
     */
    public function __construct(
        public Currency $currency,
        public int $amount,
    ) {
    }
}
