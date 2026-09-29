<?php

namespace App\SplitExpense\Http\V1\Feed;

use App\Core\Enum\Group;
use App\SplitExpense\Entity\SeAdjustment;
use App\SplitExpense\Entity\SeExpense;
use App\SplitExpense\Enum\SeFeedTypeEnum;
use Symfony\Component\Serializer\Attribute\Groups;

#[Groups([Group::public->value])]
readonly class SeFeedItem
{
    /**
     * @param SeExpense|null $expense Set when type is "expense".
     * @param SeAdjustment|null $adjustment Set when type is "adjustment".
     */
    public function __construct(
        public SeFeedTypeEnum $type,
        public ?SeExpense $expense = null,
        public ?SeAdjustment $adjustment = null,
    ) {
    }
}
