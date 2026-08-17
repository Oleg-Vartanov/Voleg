<?php

namespace App\SplitExpense\ValueObject;

/**
 * An amount between the current user and one counterparty, in one currency.
 */
readonly class SeBalanceEntry
{
    /**
     * @param int $amount Minor units. The direction is defined by whoever produced the
     *                    entry: repository sums are always positive, netted entries are
     *                    positive when the current user is owed.
     */
    public function __construct(
        public int $userId,
        public int $currencyId,
        public int $amount,
    ) {
    }
}
