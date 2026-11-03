<?php

namespace App\SplitExpense\Http\V1\Balance;

use App\Core\Enum\Group;
use Symfony\Component\Serializer\Attribute\Groups;

#[Groups([Group::public->value])]
readonly class BalanceResponse
{
    /**
     * @param BalanceAmount[] $totalAmounts Net balance per currency across every counterparty.
     * @param UserBalance[] $byUserAmounts Counterparties with a non-zero balance.
     */
    public function __construct(
        public array $totalAmounts = [],
        public array $byUserAmounts = [],
    ) {
    }
}
