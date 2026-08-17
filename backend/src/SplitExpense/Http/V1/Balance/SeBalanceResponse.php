<?php

namespace App\SplitExpense\Http\V1\Balance;

use App\Core\Enum\Group;
use Symfony\Component\Serializer\Attribute\Groups;

#[Groups([Group::public->value])]
readonly class SeBalanceResponse
{
    /**
     * @param SeBalanceAmount[] $totalAmounts Net balance per currency across every counterparty.
     * @param SeUserBalance[] $byUserAmounts Counterparties with a non-zero balance.
     */
    public function __construct(
        public array $totalAmounts = [],
        public array $byUserAmounts = [],
    ) {
    }
}
