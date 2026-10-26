<?php

namespace App\SplitExpense\Http\V1\Insights;

use App\Core\Entity\Currency;
use App\Core\Enum\Group;
use App\SplitExpense\Entity\SeCategory;
use Symfony\Component\Serializer\Attribute\Groups;

#[Groups([Group::public->value])]
readonly class SeCategoryTotal
{
    /**
     * @param int $amount The current user's total split amount in minor units.
     */
    public function __construct(
        /** Month in YYYY-MM format. */
        public string $month,
        public SeCategory $category,
        public Currency $currency,
        public int $amount,
    ) {
    }
}
