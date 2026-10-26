<?php

namespace App\SplitExpense\Http\V1\Insights;

use App\Core\Enum\Group;
use Symfony\Component\Serializer\Attribute\Groups;

#[Groups([Group::public->value])]
readonly class SeInsightsResponse
{
    /**
     * @param SeCategoryTotal[] $categoryTotals Current user's expense share by month, category and
     *                                           currency.
     */
    public function __construct(
        public array $categoryTotals = [],
        public int $totalMonthCount = 0,
    ) {
    }
}
