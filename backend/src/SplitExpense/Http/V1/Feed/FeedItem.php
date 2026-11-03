<?php

namespace App\SplitExpense\Http\V1\Feed;

use App\Core\Enum\Group;
use App\SplitExpense\Entity\Adjustment;
use App\SplitExpense\Entity\Expense;
use App\SplitExpense\Enum\FeedTypeEnum;
use Symfony\Component\Serializer\Attribute\Groups;

#[Groups([Group::public->value])]
readonly class FeedItem
{
    /**
     * @param Expense|null $expense Set when type is "expense".
     * @param Adjustment|null $adjustment Set when type is "adjustment".
     */
    public function __construct(
        public FeedTypeEnum $type,
        public ?Expense $expense = null,
        public ?Adjustment $adjustment = null,
    ) {
    }
}
