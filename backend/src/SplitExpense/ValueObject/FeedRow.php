<?php

namespace App\SplitExpense\ValueObject;

use App\SplitExpense\Enum\FeedTypeEnum;

readonly class FeedRow
{
    public function __construct(
        public FeedTypeEnum $type,
        public int $id,
    ) {
    }
}
