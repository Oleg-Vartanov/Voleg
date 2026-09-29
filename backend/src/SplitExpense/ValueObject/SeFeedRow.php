<?php

namespace App\SplitExpense\ValueObject;

use App\SplitExpense\Enum\SeFeedTypeEnum;

readonly class SeFeedRow
{
    public function __construct(
        public SeFeedTypeEnum $type,
        public int $id,
    ) {
    }
}
