<?php

namespace App\SplitExpense\ValueObject;

readonly class SeCategoryTotalEntry
{
    /**
     * @param int $amount The current user's total split amount in minor units.
     */
    public function __construct(
        public string $month,
        public int $categoryId,
        public int $currencyId,
        public int $amount,
    ) {
    }
}
