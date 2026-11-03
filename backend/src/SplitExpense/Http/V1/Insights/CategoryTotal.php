<?php

namespace App\SplitExpense\Http\V1\Insights;

use App\Core\Entity\Currency;
use App\Core\Enum\Group;
use App\SplitExpense\Entity\Category;
use Symfony\Component\Serializer\Attribute\Groups;

#[Groups([Group::public->value])]
readonly class CategoryTotal
{
    /**
     * @param int $amount Total expense cost paid by the selected payers, in minor units.
     */
    public function __construct(
        /** Month in YYYY-MM format. */
        public string $month,
        public Category $category,
        public Currency $currency,
        public int $amount,
    ) {
    }
}
